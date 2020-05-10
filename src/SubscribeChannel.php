<?php


namespace MuCTS\Laravel\WeAppSubscribeNotification;


use MuCTS\Laravel\WeAppSubscribeNotification\PriTmpl\PriTmpl;
use MuCTS\Laravel\WeAppSubscribeNotification\PriTmpl\PriTmplKeywords;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Overtrue\LaravelWeChat\Facade as EasyWeChat;
use Illuminate\Support\Collection;

class SubscribeChannel
{
    const CACHE_SUBSCRIBE_TMPL_TID_KEY = 'WE:APP:SUB:TID:%s';


    public function send($notifiable, SubscribeNotification $notification): void
    {
        $message = $notification->toWeAppSubscribeMessage($notifiable);
        if (is_null($message)) {
            return;
        }
        $collect = $notifiable->routeNotificationFor($message->getRouteNotificationFor());
        if (is_null($collect)) {
            return;
        }
        $collect = is_string($collect) ? collect([$collect]) : $collect;
        if ($collect instanceof Collection && $collect->isNotEmpty()) {
            // 从 $notification 获取小程序 app id
            $appId = $notification->getWeappId($notifiable);
            // 模板库标题Id或者关键词不能为空
            if (is_null($message->getTid()) || count($message->getKeywords()) == 0) {
                info(sprintf('send message to get template library Title cannot be empty;tid:%s,keywords:%s', $message->getTid(), json_encode($message->getKeywords(), JSON_UNESCAPED_UNICODE)));
                return;
            }
            $priTmpl = (new SubscribeTemple())
                ->setAppId($appId)
                ->setMessage($message)
                ->getPriTemp();
            if (is_null($priTmpl)) {
                info(sprintf('send subscribe template info is null;tid:%s,keywords:%s', $message->getTid(), json_encode($message->getKeywords())));
                return;
            }
            $message->setPriTmpl($priTmpl);

            $collect->each(function (string $toUser) use ($message, $appId, $notification, $notifiable) {
                if (is_null($toUser)) {
                    info('no available to user:' . $toUser);
                    return;
                }
                if (!SubscribeAuthorize::hadAutoPriTmplId($appId, $message->getPriTmplId(), $notification->getScene($notifiable), $notification->getSceneId($notifiable), $toUser)) {
                    info(sprintf('User not authorized,pri_tmpl_id:%s,openid:%s', $message->getPriTmplId(), $toUser));
                    return;
                }
                $message->setToUser($toUser);

                $rateLimitKey = $message->getRateLimit('key');
                // 若消息有发送频率限制，检查缓存是否发送间隔过短
                if (self::isRateLimited($rateLimitKey)) {
                    info('exceed rate limit, do not send. open id:' . $toUser);
                    return;
                }

                $miniProgram = EasyWeChat::miniProgram($this->getConfigName($appId));
                $res = $miniProgram->subscribe_message->send($message->toArray());
                if ($res['errcode'] != 0) {
                    throw new \Exception($res['errmsg'], $res['errcode']);
                }
                // 若消息有发送频率限制，设置缓存
                self::setRateLimited($rateLimitKey, $message->getRateLimit('tts'));
                SubscribeAuthorize::decrPriTmplId($appId, $message->getPriTmplId(), $notification->getScene($notifiable), $notification->getSceneId($notifiable), $toUser);
            });
        }
    }

    /**
     * 获取微信小程序配置信息
     *
     * @param string $appId
     * @return string
     */
    private function getConfigName(string $appId): string
    {
        return collect(config('wechat.mini_program'))->search(function ($item) use ($appId) {
            return Arr::get($item, 'app_id') == $appId;
        }) ?: '';
    }

    /**
     * 添加模版消息
     *
     * @param SubscribeTemple $subscribeTemple
     * @return PriTmpl
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addPriTmpl(SubscribeTemple $subscribeTemple)
    {
        $miniProgram = EasyWeChat::miniProgram($this->getConfigName($subscribeTemple->getAppId()));
        $keywords = (new PriTmplKeywords($subscribeTemple->getTid(), $subscribeTemple->getType(), $subscribeTemple->getName(), $this->getPriTmplKeyWords($subscribeTemple->getAppId(), $subscribeTemple->getTid())))->setKeywords($subscribeTemple->getKeywords());
        $res = $miniProgram->subscribe_message->addTemplate($subscribeTemple->getTid(), $keywords->getKids(), $subscribeTemple->getSceneValue());
        if (isset($res['errcode']) && $res['errcode'] != 0) {
            throw new \Exception($res['errmsg'], $res['errcode']);
        }
        return (new PriTmpl())->setPriTmplId($res['priTmplId'])->setPriTmplKeywords($keywords);

    }

    /**
     * 获取模版关键词信息
     *
     * @param string $appId
     * @param string $tid
     * @return array|null
     */
    public function getPriTmplKeyWords(string $appId, string $tid): ?array
    {
        $cacheKey = sprintf(self::CACHE_SUBSCRIBE_TMPL_TID_KEY, $tid);
        return Cache::rememberForever($cacheKey, function () use ($appId, $tid) {
            $miniProgram = EasyWeChat::miniProgram($this->getConfigName($appId));
            $res = $miniProgram->subscribe_message->getTemplateKeywords($tid);
            if (isset($res['errcode']) && $res['errcode'] != 0) {
                throw new \Exception($res['errmsg'], $res['errcode']);
            }
            return $res['data'];
        });
    }

    /**
     * 检查模板消息是否触发发送频率限制
     *
     * @param string|null $key
     * @return bool
     */
    public static function isRateLimited(?string $key): bool
    {
        if (!$key) return false;
        return Cache::has($key) && boolval(Cache::get($key));
    }

    /**
     * 若消息有发送频率限制，设置缓存
     *
     * @param string|null $key
     * @param int|null $tts
     *
     * @param void
     */
    public static function setRateLimited(?string $key, ?int $tts): void
    {
        if (is_null($key)) return;
        if (is_numeric($tts)) {
            Cache::put($key, 1, $tts);
            return;
        }
        Cache::forever($key, 1);
    }
}
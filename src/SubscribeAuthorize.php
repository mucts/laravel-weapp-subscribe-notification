<?php


namespace Friendsmore\laravelWeAppSubscribeNotification;


use Carbon\Carbon;
use Illuminate\Notifications\RoutesNotifications;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SubscribeAuthorize
{
    const CACHE_FOR_AUTHORIZE = 'WE_APP_SUBSCRIBE_FOR_AUTHORIZE:%s';
    const CACHE_FOR_AUTH_RESULT = 'WE_APP_SUBSCRIBE_FOR_AUTHORIZE_RESULT:%s:%s:%s';

    protected static function generateGuid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = chr(123)
                . substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12)
                . chr(125);
            return $uuid;
        }
    }

    /**
     * 获取授权列表
     *
     * @param RoutesNotifications $notifiable
     * @param Collection $notifications
     * @param Carbon|null $expiryTime
     * @return array
     */
    public static function getSubscribeTmpl(RoutesNotifications $notifiable, Collection $notifications, ?Carbon $expiryTime): array
    {
        $authNo = self::generateGuid();
        /** @var Collection $priTemplates */
        $priTemplates = Cache::remember(sprintf(self::CACHE_FOR_AUTHORIZE, $authNo), Carbon::now()->addSeconds(1800), function () use ($notifiable, $notifications, $expiryTime) {
            return $notifications->map(function (SubscribeNotification $notification) use ($notifiable, $expiryTime) {
                return [
                    'auth_no' => $notification->getAuthorizedNo($notifiable),
                    'app_id' => $notification->getWeappId($notifiable),
                    'pri_tmpl_id' => $notification->getPriTmpl($notifiable)->getPriTmplId(),
                    'expiry_time' => $expiryTime,
                ];
            });
        });
        return [
            'auth_no' => $authNo,
            'pri_tmpl_ids' => $priTemplates->pluck('pri_tmpl_id')
        ];
    }

    /**
     * 校验授权结果
     *
     * @param string $authNo
     * @param array $priTmplIdsResult
     * @param string $openId
     */
    public static function subscribeAuthorizationResult(string $authNo, array $priTmplIdsResult, string $openId): void
    {
        $cacheKey = sprintf(self::CACHE_FOR_AUTHORIZE, $authNo);
        /** @var Collection $authPriTmplIds */
        $authPriTmplIds = Cache::has($cacheKey) ? Cache::forget($cacheKey) : collect();
        if ($authPriTmplIds->isNotEmpty()) {
            return;
        }
        $acceptPriTmplIds = collect($priTmplIdsResult)
            ->filter(function ($value) {
                return Str::lower($value) == 'accept';
            })
            ->keys()
            ->all();
        $authPriTmplIds->each(function ($item) use ($acceptPriTmplIds, $openId) {
            if (isset($item['pri_tmpl_id'])
                && isset($item['app_id'])
                && isset($item['auth_no'])
                && isset($item['expiry_time'])
                && in_array($item['pri_tmpl_id'], $acceptPriTmplIds)) {
                $cacheKey = self::getCacheKey($item['app_id'], $item['auth_no'], $openId);
                if (is_null($item['expiry_time'])) {
                    Cache::forever($cacheKey, $item['pri_tmpl_id']);
                } else {
                    Cache::put($cacheKey, $item['pri_tmpl_id'], $item['expiry_time']);
                }
            }
        });
    }

    /**
     * 获取授权缓存KEY
     *
     * @param string $appId
     * @param string $authNo
     * @param string $openId
     * @return string
     */
    public static function getCacheKey(string $appId, string $authNo, string $openId)
    {
        return sprintf(self::CACHE_FOR_AUTH_RESULT, $appId, $authNo, $openId);
    }

    /**
     * 判断是否授权
     *
     * @param string $appId
     * @param string $authNo
     * @param string $openId
     * @return bool
     */
    public static function hadAutoPriTmplId(string $appId, string $authNo, string $openId)
    {
        $cacheKey = self::getCacheKey($appId, $authNo, $openId);
        return Cache::has($cacheKey);
    }

    /**
     * 获取删除授权模版ID
     *
     * @param string $appId
     * @param string $authNo
     * @param string $openId
     * @return bool|null
     */

    public static function getPriTmplId(string $appId, string $authNo, string $openId)
    {
        $cacheKey = self::getCacheKey($appId, $authNo, $openId);
        return Cache::has($cacheKey) ? Cache::forget($cacheKey) : null;
    }
}
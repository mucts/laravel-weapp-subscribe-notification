<?php


namespace Friendsmore\LaravelWeAppSubscribeNotification\Models;


use Friendsmore\LaravelBase\Model;
use Friendsmore\LaravelWeAppSubscribeNotification\PriTmpl\PriTmpl;
use Friendsmore\LaravelWeAppSubscribeNotification\PriTmpl\PriTmplKeywords;
use Friendsmore\LaravelWeAppSubscribeNotification\SubscribeChannel;
use Friendsmore\LaravelWeAppSubscribeNotification\SubscribeTemple;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\WeappSubscribeNotifications
 *
 * @property bool $id 微信小程序订阅消息模版 ID
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $app_id 小程序appid
 * @property string $tid 模板库标题ID
 * @property string|null $title 模版标题
 * @property string $pri_tmpl_id 订阅模版ID
 * @property string $hash 模板标识(模板标题ID与模板关键词列表MD5产生)
 * @property array $content 模版内容，格式:[{"kid":"2","name":"会议时间","rule":"date"}]
 * @property string $type 模版类型，2 one_time 为一次性订阅|3 long_term 为长期订阅
 * @property array $scenes 授权场景
 * @method static Builder|WeAppSubscribeNotification newModelQuery()
 * @method static Builder|WeAppSubscribeNotification newQuery()
 * @method static Builder|WeAppSubscribeNotification query()
 * @method static Builder|WeAppSubscribeNotification whereAppId($value)
 * @method static Builder|WeAppSubscribeNotification whereContent($value)
 * @method static Builder|WeAppSubscribeNotification whereCreatedAt($value)
 * @method static Builder|WeAppSubscribeNotification whereId($value)
 * @method static Builder|WeAppSubscribeNotification whereHash($value)
 * @method static Builder|WeAppSubscribeNotification wherePriTmplId($value)
 * @method static Builder|WeAppSubscribeNotification whereTid($value)
 * @method static Builder|WeAppSubscribeNotification whereTitle($value)
 * @method static Builder|WeAppSubscribeNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WeAppSubscribeNotification extends Model
{
    const CACHE_FOR_WE_APP_TMPL_NOTIFICATIONS = 'CACHE_FOR_WE_APP_SUBSCRIBE_TMPL_NOTIFICATIONS:%s';
    const CACHE_FOR_TAGS = ['we_app_subscribe_tmpl'];

    const TYPES = [
        2 => "one_time", // 为一次性订阅
        3 => 'long_term', // 为长期订阅
    ];

    protected $table = 'weapp_subscribe_notifications';
    protected $casts = [
        'app_id' => 'string',
        'pri_tmpl_id' => 'string',
        'content' => 'array',
        'scenes' => 'array',
        'type' => 'enum',
        'tid' => 'string',
        'md5' => 'string'
    ];

    protected $dates = [];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public static function updateOrCreatePriTmpl(SubscribeTemple $subscribeTemple): bool
    {
        $template = self::whereAppId($subscribeTemple->getAppId())->whereHash($subscribeTemple->getHash())->first();
        if ($template) {
            if (collect($template->scenes)->diffAssoc($subscribeTemple->getScenes())->isNotEmpty()) {
                $template->update(['scenes' => $subscribeTemple->getScenes()]);
            }
        } else {
            $priTmpl = (new SubscribeChannel())->addPriTmpl($subscribeTemple);
            $template = self::create([
                'app_id' => $subscribeTemple->getAppId(),
                'tid' => $subscribeTemple->getTid(),
                'title' => $subscribeTemple->getName(),
                'type' => $subscribeTemple->getType(),
                'pri_tmpl_id' => $priTmpl->getPriTmplId(),
                'hash' => $subscribeTemple->getHash(),
                'content' => $priTmpl->getPriTmplKeywords()->getContent()->values()->toArray(),
                'scenes' => $subscribeTemple->getScenes()
            ]);
        }
        if (is_null($template)) {
            return false;
        }
        $cacheKey = self::getCacheKey($template->hash);
        if (Cache::tags(self::getCacheTags())->has($cacheKey)) {
            Cache::tags(self::getCacheTags())->forget($cacheKey);
        }
        return true;

    }

    /**
     * 获取模版信息
     *
     * @param string $hash
     * @return PriTmpl|null
     */
    public static function getPriTmpl(string $hash): ?PriTmpl
    {
        return Cache::tags(self::getCacheTags())->rememberForever(self::getCacheKey($hash), function () use ($hash) {
            $template = self::whereHash($hash)->first();
            if (is_null($template)) {
                return null;
            }
            return (new PriTmpl())
                ->setPriTmplId($template->pri_tmpl_id)
                ->setPriTmplKeywords((new PriTmplKeywords($template->tid, $template->type, $template->title, $template->content)));
        });
    }


    public static function getCacheTags()
    {
        return self::CACHE_FOR_TAGS;
    }

    private static function getCacheKey(string $hash)
    {
        return sprintf(self::CACHE_FOR_WE_APP_TMPL_NOTIFICATIONS, $hash);
    }
}

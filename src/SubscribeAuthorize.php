<?php


namespace Friendsmore\laravelWeAppSubscribeNotification;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SubscribeAuthorize
{
    const CACHE_FOR_AUTH_RESULT = 'WE_APP_SUBSCRIBE_FOR_AUTHORIZE_RESULT:%s:%s:%s:%s:%s';

    // 授权过期时间
    const EXPIRY_TIME = 30 * 24 * 60 * 60;

    /**
     * 校验授权结果
     *
     * @param string $appId
     * @param string $scene
     * @param string $sceneId
     * @param array $priTmplIdsResult
     * @param string $openId
     */
    public static function subscribeAuthorizationResult(string $appId, string $scene, string $sceneId, array $priTmplIdsResult, string $openId): void
    {
        collect($priTmplIdsResult)
            ->filter(function ($value) {
                return Str::lower($value) == 'accept';
            })
            ->keys()
            ->map(function ($priTmpId) use ($appId, $scene, $sceneId, $openId) {
                $cacheKey = self::getCacheKey($appId, $scene, $priTmpId, $sceneId, $openId);
                Cache::put($cacheKey, $priTmpId, self::EXPIRY_TIME);
            });
    }

    /**
     * 获取授权缓存KEY
     *
     * @param string $appId
     * @param string $scene
     * @param string $priTmplId
     * @param string $sceneId
     * @param string $openId
     * @return string
     */
    public static function getCacheKey(string $appId, string $scene, string $priTmplId, ?string $sceneId, string $openId)
    {
        return sprintf(self::CACHE_FOR_AUTH_RESULT, $appId, $priTmplId, $scene, $sceneId, $openId);
    }

    /**
     * 判断是否授权
     *
     * @param string $appId
     * @param string $priTmplId
     * @param string $scene
     * @param string $sceneId
     * @param string $openId
     * @return bool
     */
    public static function hadAutoPriTmplId(string $appId, string $priTmplId, string $scene, ?string $sceneId, string $openId)
    {
        $cacheKey = self::getCacheKey($appId, $priTmplId, $scene, $sceneId, $openId);
        return Cache::has($cacheKey);
    }

    /**
     * 获取删除授权模版ID
     *
     * @param string $appId
     * @param string $priTmplId
     * @param string $scene
     * @param string|null $sceneId
     * @param string $openId
     * @return bool|null
     */

    public static function getPriTmplId(string $appId, string $priTmplId, string $scene, ?string $sceneId, string $openId)
    {
        $cacheKey = self::getCacheKey($appId, $scene, $priTmplId, $sceneId, $openId);
        return Cache::has($cacheKey) ? Cache::forget($cacheKey) : null;
    }
}
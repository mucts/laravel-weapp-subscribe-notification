<?php


namespace MuCTS\Laravel\WeAppSubscribeNotification;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SubscribeAuthorize
{
    const CACHE_FOR_AUTH_RESULT = 'WE:APP:SUB:RES:%s';

    // 授权过期时间
    const EXPIRY_TIME = 6 * 30 * 24 * 60 * 60;

    /**
     * 校验授权结果
     *
     * @param string $appId
     * @param string $scene
     * @param string $sceneId
     * @param array $priTmplIdsResult
     * @param string $openId
     * @return int
     */
    public static function subscribeAuthorizationResult(string $appId, string $scene, ?string $sceneId, array $priTmplIdsResult, string $openId): int
    {
        return collect($priTmplIdsResult)
            ->filter(function ($value) {
                return Str::lower($value) == 'accept';
            })
            ->keys()
            ->map(function ($priTmpId) use ($appId, $scene, $sceneId, $openId) {
                $cacheKey = self::getCacheKey($appId, $priTmpId, $scene, $sceneId, $openId);
                self::incrPriTmplCache($cacheKey);
            })->count();
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
    public static function getCacheKey(string $appId, string $priTmplId, string $scene, ?string $sceneId, string $openId)
    {
        return sprintf(self::CACHE_FOR_AUTH_RESULT, md5(json_encode([$appId, $priTmplId, $scene, strval($sceneId), $openId], JSON_UNESCAPED_UNICODE)));
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
        if (self::hadAuthorization($cacheKey)) {
            return true;
        }
        $cacheKey = self::getCacheKey($appId, $priTmplId, $scene, null, $openId);
        return self::hadAuthorization($cacheKey);
    }

    /**
     * 获取删除授权模版ID
     *
     * @param string $appId
     * @param string $priTmplId
     * @param string $scene
     * @param string|null $sceneId
     * @param string $openId
     */

    public static function decrPriTmplId(string $appId, string $priTmplId, string $scene, ?string $sceneId, string $openId)
    {
        $cacheKey = self::getCacheKey($appId, $priTmplId, $scene, $sceneId, $openId);
        if (self::hadAuthorization($cacheKey)) {
            self::decrPriTmplCache($cacheKey);
        }
        $cacheKey = self::getCacheKey($appId, $priTmplId, $scene, null, $openId);
        self::hadAuthorization($cacheKey) && self::decrPriTmplCache($cacheKey);
    }

    private static function hadAuthorization(string $cacheKey)
    {
        $redis = Redis::connection();
        return $redis->exists($cacheKey) ? true : false;
    }

    private static function incrPriTmplCache(string $cacheKey, int $incr = 1): void
    {
        $redis = Redis::connection();
        $redis->incrby($cacheKey, $incr);
        $redis->expire($cacheKey, self::EXPIRY_TIME);
    }

    private static function decrPriTmplCache(string $cacheKey, int $decr = 1): void
    {
        $redis = Redis::connection();
        $redis->decrby($cacheKey, $decr);
        $value = $redis->get($cacheKey);
        if ($value <= 0) {
            $redis->del([$cacheKey]);
        }
    }
}
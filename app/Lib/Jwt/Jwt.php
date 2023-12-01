<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Lib\Jwt;

use Carbon\Carbon;
use Firebase\JWT\Key;

class Jwt
{
    // 可用的签名算法
    private const ES384 = 'ES384';

    private const ES256 = 'ES256';

    private const ES256K = 'ES256K';

    private const HS256 = 'HS256';

    private const HS384 = 'HS384';

    private const HS512 = 'HS512';

    private const RS256 = 'RS256';

    private const RS384 = 'RS384';

    private const RS512 = 'RS512';

    // TODO:
    // 1、如果你想让某些jwt主动失效, 那么需要进行存储, 然后对其删除或者变更, 那么之前颁发的jwt将无法通过验证
    // 2、如果想实现自动延时, 那么可以颁发两个jwt(access_token, refresh_token), 两个jwt有效期不一样,
    // access_token 失效后验证 refresh_token, 通过后再次颁发access_token

    /**
     * 获取jwt.
     */
    public static function createJwt(array|string $data): string
    {
        $key = \Hyperf\Support\env('JWT_KEY', 'hyperf');
        $now = Carbon::now()->timestamp;
        $payload = [
            'iss' => \Hyperf\Support\env('APP_DOMAIN', 'hyperf'), // 颁发者,
            'sub' => 'accredit', // 主题,
            'aud' => 'pc', // 接收者
            'iat' => $now, // jwt发出的时间
            'nbf' => $now + 1, // jwt的开始处理的时间(颁发jwt后一秒才能进行解析jwt)
            'exp' => $now + 60 * 60, // 到期时间
            'data' => is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data,
        ];
        $head = [
            'typ' => 'JWT', // 令牌类型
            'cty' => '', // 内容类型
            'kid' => null, // 秘钥标识
        ];
        $keyId = null;
        return \Firebase\JWT\JWT::encode($payload, $key, self::HS256, $keyId, $head);
    }

    /**
     * 解析jwt.
     */
    public static function explainJwt(string $jwt): array
    {
        $key = \Hyperf\Support\env('JWT_KEY', 'hyperf');
        $decode = \Firebase\JWT\JWT::decode($jwt, new Key($key, self::HS256));
        $decode = get_object_vars($decode);
        if (isset($decode['data'])) {
            $decode['data'] = json_decode($decode['data']) ?? $decode['data'];
        }
        return $decode;
    }
}

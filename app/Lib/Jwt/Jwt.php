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

    /**
     * 获取jwt.
     * @param array|string $data
     * @return string
     */
    public static function createJwt(array|string $data): string
    {
        $key = \Hyperf\Support\env('JWT_KEY', 'hyperf');
        $now = Carbon::now()->timestamp;
        $payload = [
            'iss' => 'api.tzf-foryou.xyz', // 颁发者,
            'sub' => 'accredit', // 主题,
            'aud' => 'pc', // 接收者
            'iat' => $now, // jwt发出的时间
            'nbf' => $now + 1, // jwt的开始处理的时间
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
     * @param string $jwt
     * @return array
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

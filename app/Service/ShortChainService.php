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

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Lib\Algorithm\Murmur;
use App\Model\ShortChain;
use Carbon\Carbon;
use Hyperf\Stringable\Str;

class ShortChainService extends AbstractService
{
    /**
     * 长链接转换短连接.
     * @param int $uid 用户ID
     * @param string $url 待转换url
     * @param int $ttl 有效时间长度(秒)
     * @return string 短链
     */
    public function convert(int $uid, string $url, int $ttl = 60): string
    {
        $hashCode = Murmur::from10To62(Murmur::hash3Int($url));
        $appDomain = \Hyperf\Support\env('APP_DOMAIN', 'https://api.tzf-foryou.xyz');
        /** @var ShortChain $chain */
        $shortChain = ShortChain::query()
            ->where(['hash_code' => $hashCode, 'uid' => $uid, 'status' => ShortChain::STATUS_ACTIVE])
            ->first();
        if ($shortChain !== null) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::SHORT_CHAIN_EXIST));
        }
        $chain = new ShortChain();
        $chain->uid = $uid;
        $chain->url = $url;
        $chain->short_chain = $appDomain . '/' . $hashCode;
        $chain->hash_code = $hashCode;
        $chain->expire_at = Carbon::now()->addSeconds($ttl)->toDateTimeString();
        $chain->save();

        return $appDomain . '/' . $hashCode;
    }

    /**
     * 短链匹配对应的原始链接.
     * @param string $shortChain 短连接
     * @param int|null $uid
     * @return string 原始链接
     */
    public function reConvert(?int $uid,string $shortChain): string
    {
        $hashCode = Str::after(parse_url($shortChain, PHP_URL_PATH), '/');
        /** @var ShortChain $chain */
        $chain = ShortChain::query()->where(['hash_code' => $hashCode])
            ->when($uid, function ($query, $uid) {
                $query->where(['uid' => $uid]);
            })->first();
        if ($chain === null) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::UNKNOWN_SHORT_CHAIN));
        }
        // 短链已过期
        if (Carbon::createFromFormat('Y-m-d H:i:s', $chain->expire_at)->timestamp < time()) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::SHORT_CHAIN_EXPIRED));
        }

        return $chain->url;
    }
}

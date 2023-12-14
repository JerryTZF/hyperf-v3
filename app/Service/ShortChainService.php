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
}

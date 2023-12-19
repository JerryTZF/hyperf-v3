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
use App\Lib\Math\Math;
use App\Model\Goods;
use App\Model\Orders;

class LockService extends AbstractService
{
    public function createOrderWithoutLock(int $uid, int $gid, int $number = 1): string
    {
        /** @var Goods $goodInfo */
        $goodInfo = Goods::query()->where(['id' => $gid])->first();
        // 商品不存在
        if ($goodInfo === null) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::GOOD_NOT_FOUND));
        }
        // 库存不足
        if ($goodInfo->stock < $number) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::GOOD_STOCK_EMPTY, [$goodInfo->name]));
        }

        // 创建订单
        $orderNo = $this->createOrderNo();
        (new Orders([
            'uid' => $uid,
            'gid' => $gid,
            'order_no' => $orderNo,
            'number' => $number,
            'payment_money' => Math::mul($goodInfo->price, $number),
        ]))->save();

        // 扣减库存
        $goodInfo->stock = $goodInfo->stock - $number;
        $goodInfo->save();

        return $orderNo;
    }

    private function createOrderNo(): string
    {
        return bcmul((string) microtime(true), (string) 1000) . mt_rand(10000, 99999);
    }
}

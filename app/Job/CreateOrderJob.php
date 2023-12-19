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

namespace App\Job;

use App\Lib\Log\Log;
use App\Lib\Math\Math;
use App\Model\Goods;
use App\Model\Orders;

class CreateOrderJob extends AbstractJob
{
    /**
     * 并行消费数为1的队列创建订单.
     */
    public function handle()
    {
        [$gid, $num, $uid, $orderNo] = [
            $this->params['gid'],
            $this->params['num'],
            $this->params['uid'],
            $this->params['order_no'],
        ];
        /** @var Goods $goodInfo */
        $goodInfo = Goods::query()->where(['id' => $gid])->first();
        // 商品不存在
        if ($goodInfo === null) {
            Log::warning('商品不存在', $this->params);
            return null;
        }
        // 库存不足
        if ($goodInfo->stock < $num) {
            Log::warning('库存不足', $this->params);
            return null;
        }

        // 创建订单
        (new Orders([
            'uid' => $uid,
            'gid' => $gid,
            'order_no' => $orderNo,
            'number' => $num,
            'payment_money' => Math::mul($goodInfo->price, $num),
        ]))->save();

        // 扣减库存
        $goodInfo->stock = $goodInfo->stock - $num;
        $goodInfo->save();
    }
}

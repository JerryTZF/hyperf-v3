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

use App\Constants\ConstCode;
use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Job\CreateOrderJob;
use App\Lib\Math\Math;
use App\Lib\RedisQueue\RedisQueueFactory;
use App\Model\Goods;
use App\Model\Orders;
use Hyperf\Coroutine\Coroutine;
use Hyperf\DbConnection\Db;
use Throwable;

class LockService extends AbstractService
{
    /**
     * 不加锁的创建订单并扣减库存(外部请加锁!!!).
     * @param int $uid 用户id
     * @param int $gid 商品id
     * @param int $number 购买数量
     * @return string 订单编号
     */
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
        $orderNo = Math::getUniqueId();
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

    /**
     * 根据库存条件创建订单(条件乐观锁).
     * @param int $uid 用户id
     * @param int $gid 商品id
     * @param int $number 购买数量
     * @return string 订单编号
     */
    public function createOrderWithCondition(int $uid, int $gid, int $number = 1): string
    {
        // 开启事务
        Db::beginTransaction();
        try {
            // 非常典型的库存安全检验, 适用于库存模型(也算是乐观锁的一种变形)
            // 标准的乐观锁会出现大量请求进来, 但是有很大可能只有其中的部分人操作成功, 虽然不会影响数据正确性, 但是效率较低.
            // 乐观锁的更新操作, 最好用主键或者唯一索引来更新, 这样是行锁, 否则更新时会锁表.
            // SQL: UPDATE `goods` SET `stock` = `stock` - 2, `goods`.`update_time` = '2023-12-19 22:50:06'
            // WHERE (`id` = '4') AND `stock` >= '2'
            $effectRows = Goods::query()
                ->where(['id' => $gid])
                ->where('stock', '>=', $number)
                ->decrement('stock', $number);
            if ($effectRows > 0) {
                $price = Goods::query()->where(['id' => $gid])->value('price');
                // 创建订单
                $orderNo = Math::getUniqueId();
                (new Orders([
                    'uid' => $uid,
                    'gid' => $gid,
                    'order_no' => $orderNo,
                    'number' => $number,
                    'payment_money' => Math::mul($price, $number),
                ]))->save();
            } else {
                $orderNo = '';
            }
            // 提交事务
            Db::commit();
        } catch (Throwable $e) {
            $orderNo = '';
            Db::rollBack();
        }

        if ($orderNo === '') {
            throw new BusinessException(...self::getErrorMap(ErrorCode::STOCK_EMPTY));
        }

        return $orderNo;
    }

    /**
     * 根据版本控制创建订单(版本控制乐观锁).
     * @param int $uid 用户id
     * @param int $gid 商品id
     * @param int $number 购买数量
     * @return string 订单编号
     */
    public function createOrderWithVersion(int $uid, int $gid, int $number = 1): string
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

        $effectRows = Goods::query()
            ->where(['id' => $gid, 'version' => $goodInfo->version]) // 如果该版本已经被修改,那么更新条件无法命中
            ->update([
                'stock' => $goodInfo->stock - $number,
                'version' => $goodInfo->version + 1, // 新版本
            ]);
        if ($effectRows > 0) {
            $price = Goods::query()->where(['id' => $gid])->value('price');
            // 创建订单
            $orderNo = Math::getUniqueId();
            (new Orders([
                'uid' => $uid,
                'gid' => $gid,
                'order_no' => $orderNo,
                'number' => $number,
                'payment_money' => Math::mul($price, $number),
            ]))->save();
        } else {
            // 一次没有抢到库存就被退出.
            $orderNo = '';
        }

        if ($orderNo === '') {
            throw new BusinessException(...self::getErrorMap(ErrorCode::STOCK_EMPTY));
        }

        return $orderNo;
    }

    /**
     * 版本控制+自旋 创建订单(乐观锁+自旋).
     * @param int $uid 用户id
     * @param int $gid 商品id
     * @param int $number 购买数量
     * @return string 订单编号
     */
    public function createOrderWithSpin(int $uid, int $gid, int $number = 1): string
    {
        // 自旋1000次
        $spinTimes = 1000;
        $orderNo = '';
        while ($spinTimes > 0) {
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

            $effectRows = Goods::query()
                ->where(['id' => $gid, 'version' => $goodInfo->version]) // 如果该版本已经被修改,那么更新条件无法命中
                ->update([
                    'stock' => $goodInfo->stock - $number,
                    'version' => $goodInfo->version + 1, // 新版本
                ]);
            if ($effectRows > 0) {
                $price = Goods::query()->where(['id' => $gid])->value('price');
                // 创建订单
                $orderNo = Math::getUniqueId();
                (new Orders([
                    'uid' => $uid,
                    'gid' => $gid,
                    'order_no' => $orderNo,
                    'number' => $number,
                    'payment_money' => Math::mul($price, $number),
                ]))->save();
                break;
            }
            // 一次没有抢到继续自旋尝试.
            --$spinTimes;
        }

        if ($orderNo === '') {
            throw new BusinessException(...self::getErrorMap(ErrorCode::STOCK_EMPTY));
        }

        return $orderNo;
    }

    /**
     * 悲观锁创建订单(共享锁).
     * @param int $uid 用户id
     * @param int $gid 商品id
     * @param int $number 购买数量
     * @return string 订单编号
     */
    public function createOrderWithShareMode(int $uid, int $gid, int $number = 1): string
    {
        // 开启事务
        Db::beginTransaction();
        try {
            /** 加上共享锁 @var Goods $goodInfo */
            $goodInfo = Goods::query()->where(['id' => $gid])->sharedLock()->first();
            // 商品不存在
            if ($goodInfo === null) {
                throw new BusinessException(...self::getErrorMap(ErrorCode::GOOD_NOT_FOUND));
            }
            // 库存不足
            if ($goodInfo->stock < $number) {
                throw new BusinessException(...self::getErrorMap(ErrorCode::GOOD_STOCK_EMPTY, [$goodInfo->name]));
            }
            // 创建订单
            $orderNo = Math::getUniqueId();
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
            Db::commit();
        } catch (Throwable $e) {
            $orderNo = '';
            Db::rollBack();
        }

        if ($orderNo === '') {
            throw new BusinessException(...self::getErrorMap(ErrorCode::STOCK_EMPTY));
        }

        return $orderNo;
    }

    /**
     * 悲观锁创建订单(排它锁).
     * @param int $uid 用户id
     * @param int $gid 商品id
     * @param int $number 购买数量
     * @return string 订单编号
     */
    public function createOrderWithForUpdate(int $uid, int $gid, int $number = 1): string
    {
        // 开启事务
        Db::beginTransaction();
        try {
            $orderNo = '';

            /** 加上排它锁 @var Goods $goodInfo */
            $goodInfo = Goods::query()->where(['id' => $gid])->lockForUpdate()->first();
            // 商品不存在
            if ($goodInfo === null) {
                throw new BusinessException(...self::getErrorMap(ErrorCode::GOOD_NOT_FOUND));
            }
            // 库存不足
            if ($goodInfo->stock < $number) {
                throw new BusinessException(...self::getErrorMap(ErrorCode::GOOD_STOCK_EMPTY, [$goodInfo->name]));
            }
            // 创建订单
            $orderNo = Math::getUniqueId();
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
            Db::commit();
        } catch (Throwable $e) {
            Db::rollBack();
        }

        if ($orderNo === '') {
            throw new BusinessException(...self::getErrorMap(ErrorCode::STOCK_EMPTY));
        }

        return $orderNo;
    }

    /**
     * 使用并行消费数为1的队列创建订单(虽然会返回订单号,但是不一定购买成功).
     * @param int $uid 用户id
     * @param int $gid 商品id
     * @param int $number 购买数量
     * @return string 订单编号
     */
    public function createOrderWithLockQueue(int $uid, int $gid, int $number = 1): string
    {
        $orderNo = Math::getUniqueId();
        Coroutine::create(function () use ($uid, $gid, $number, $orderNo) {
            // 队列消费配置请看:
            // config/autoload/async_queue.php 中 ConstCode::LOCK_QUEUE_NAME 队列的 concurrent.limit配置.
            $job = new CreateOrderJob(uniqid(), [
                'uid' => $uid,
                'gid' => $gid,
                'num' => $number,
                'order_no' => $orderNo,
            ]);
            RedisQueueFactory::safePush($job, ConstCode::LOCK_QUEUE_NAME, 0);
        });

        return $orderNo;
    }
}

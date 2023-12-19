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

namespace App\Controller;

use App\Lib\Lock\RedisLock;
use App\Request\LockRequest;
use App\Service\LockService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;
use Lysice\HyperfRedisLock\LockTimeoutException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 演示各种需要锁定的场景, 经压力测试均正常工作.
 * 场景: 扣减库存(库存充足的话), 并且创建订单.
 */
#[Controller(prefix: 'lock')]
class LockController extends AbstractController
{
    #[Inject]
    protected LockService $service;

    /**
     * 创建订单(非阻塞形式锁).
     * @param LockRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     * @throws LockTimeoutException 异常
     */
    #[PostMapping(path: 'redis/sync')]
    #[Scene(scene: 'create_order')]
    public function createOrderBySyncRedisLock(LockRequest $request): array
    {
        $gid = $request->input('gid');
        $num = $request->input('number');
        $uid = $this->jwtPayload['data']['uid'];

        [$lockName, $lockingSeconds, $blockSeconds, $owner] = [
            'create_order_async_lock', // 锁名称
            3, // 闭包内方法应在3秒内完成, 超过3秒后自动释放该锁
            1, // 非阻塞该字段无效. 获取不到锁不会阻塞着尝试继续获取, 会直接返回false.
            sprintf('%s_%s_create_order_scene_sync', $uid, $gid),
        ];
        $lock = new RedisLock($lockName, $lockingSeconds, $blockSeconds, $owner);
        $orderNo = $lock->lockSync(function () use ($gid, $num, $uid) {
            return $this->service->createOrderWithoutLock($uid, intval($gid), intval($num));
        });
        if ($orderNo === false) {
            throw new LockTimeoutException();
        }

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }

    /**
     * 创建订单(阻塞形式锁).
     * @param LockRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     * @throws LockTimeoutException 阻塞超时异常
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    #[PostMapping(path: 'redis/async')]
    #[Scene(scene: 'create_order')]
    public function createOrderByAsyncRedisLock(LockRequest $request): array
    {
        $gid = $request->input('gid');
        $num = $request->input('number');
        $uid = $this->jwtPayload['data']['uid'];

        [$lockName, $lockingSeconds, $blockSeconds, $owner] = [
            'create_order_async_lock', // 锁名称
            3, // 闭包内方法应在3秒内完成, 超过3秒后自动释放该锁
            1, // 1秒内获取不到锁, 会抛出LockTimeoutException异常(每250ms尝试获取一次)
            sprintf('%s_%s_create_order_scene_async', $uid, $gid),
        ];
        $lock = new RedisLock($lockName, $lockingSeconds, $blockSeconds, $owner);
        $orderNo = $lock->lockAsync(function () use ($gid, $num, $uid) {
            return $this->service->createOrderWithoutLock($uid, intval($gid), intval($num));
        });

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }

    /**
     * 创建订单(条件乐观锁).
     * @param LockRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'optimistic/condition')]
    #[Scene(scene: 'create_order')]
    public function createOrderByCondition(LockRequest $request): array
    {
        $gid = intval($request->input('gid'));
        $num = intval($request->input('number'));
        $uid = $this->jwtPayload['data']['uid'];
        $orderNo = $this->service->createOrderWithCondition($uid, $gid, $num);

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }

    /**
     * 创建订单(版本控制乐观锁).
     * @param LockRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'optimistic/version')]
    #[Scene(scene: 'create_order')]
    public function createOrderByVersion(LockRequest $request): array
    {
        $gid = intval($request->input('gid'));
        $num = intval($request->input('number'));
        $uid = $this->jwtPayload['data']['uid'];
        $orderNo = $this->service->createOrderWithVersion($uid, $gid, $num);

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }

    /**
     * 创建订单(版本控制+自旋).
     * @param LockRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'optimistic/spin')]
    #[Scene(scene: 'create_order')]
    public function createOrderByVersionSpin(LockRequest $request): array
    {
        $gid = intval($request->input('gid'));
        $num = intval($request->input('number'));
        $uid = $this->jwtPayload['data']['uid'];
        $orderNo = $this->service->createOrderWithSpin($uid, $gid, $num);

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }

    /**
     * 使用悲观锁(排它锁)创建订单.
     * @param LockRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'pessimism/for_update')]
    #[Scene(scene: 'create_order')]
    public function createOrderByForUpdate(LockRequest $request): array
    {
        $gid = intval($request->input('gid'));
        $num = intval($request->input('number'));
        $uid = $this->jwtPayload['data']['uid'];
        $orderNo = $this->service->createOrderWithForUpdate($uid, $gid, $num);

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }

    /**
     * 使用悲观锁(共享锁)创建订单.
     * @param LockRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'pessimism/share_mode')]
    #[Scene(scene: 'create_order')]
    public function createOrderByShareMode(LockRequest $request): array
    {
        $gid = intval($request->input('gid'));
        $num = intval($request->input('number'));
        $uid = $this->jwtPayload['data']['uid'];
        $orderNo = $this->service->createOrderWithShareMode($uid, $gid, $num);

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }

    /**
     * 使用并行消费数为1的队列创建订单.
     * @param LockRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'queue/consume')]
    #[Scene(scene: 'create_order')]
    public function createOrderByLockQueue(LockRequest $request): array
    {
        $gid = intval($request->input('gid'));
        $num = intval($request->input('number'));
        $uid = $this->jwtPayload['data']['uid'];
        $orderNo = $this->service->createOrderWithLockQueue($uid, $gid, $num);

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }
}

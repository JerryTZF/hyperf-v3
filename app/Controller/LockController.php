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
 * 演示各种需要锁定的场景.
 * Class LockController.
 */
#[Controller(prefix: 'lock')]
class LockController extends AbstractController
{
    #[Inject]
    protected LockService $service;

    /**
     * 创建订单(阻塞形式锁).
     * @param LockRequest $request 请求验证器
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
            sprintf('%s_%s_create_order_scene', $uid, $gid),
        ];
        $lock = new RedisLock($lockName, $lockingSeconds, $blockSeconds, $owner);
        $orderNo = $lock->lockAsync(function () use ($gid, $num, $uid) {
            return $this->service->createOrderWithoutLock($uid, intval($gid), intval($num));
        });

        return $this->result->setData(['oder_no' => $orderNo])->getResult();
    }
}

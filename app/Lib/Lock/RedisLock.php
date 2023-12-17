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

namespace App\Lib\Lock;

use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;
use Lysice\HyperfRedisLock\LockTimeoutException;

class RedisLock
{
    /**
     * 锁名称.
     */
    private string $lockName;

    /**
     * 锁定的时间长度(该值一般应大于等于闭包执行的耗时).
     */
    private int $lockingSeconds;

    /**
     * 锁定的标志位.
     * @var null|mixed|string
     */
    private string $owner;

    /**
     * 等待获取锁的超时时间(一般小于等于$lockingSeconds).
     */
    private int $blockSeconds;

    /**
     * 锁实例.
     */
    private \Lysice\HyperfRedisLock\RedisLock $lock;

    public function __construct(string $lockName, int $lockingSeconds, int $blockSeconds = 3, $owner = null)
    {
        $this->blockSeconds = $blockSeconds;
        $this->lockName = $lockName;
        $this->lockingSeconds = $lockingSeconds;
        $this->owner = $owner;

        $redisInstance = ApplicationContext::getContainer()->get(Redis::class);
        // who(不同的客户端) 持有 what(不同业务场景) 样子的锁
        $this->lock = new \Lysice\HyperfRedisLock\RedisLock(
            $redisInstance, // Redis实例
            $this->lockName, // 锁名称
            $this->lockingSeconds, // 该锁生效的持续时间上限
            $this->owner ?? null // 谁持有该锁
        );
    }

    /**
     * 非阻塞形式锁定.
     * @param callable $func 需锁定的闭包
     * @return mixed bool:false 获取锁失败; mixed: 闭包返回的结果
     */
    public function lockSync(callable $func): mixed
    {
        return $this->lock->get($func);
    }

    /**
     * 阻塞形式锁定.
     * @throws LockTimeoutException
     */
    public function lockAsync(callable $func): mixed
    {
        return $this->lock->block($this->blockSeconds, $func);
    }
}

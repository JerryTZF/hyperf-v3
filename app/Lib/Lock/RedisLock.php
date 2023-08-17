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

use App\Lib\Redis\Redis;

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

    public function __construct(string $lockName, int $lockingSeconds, int $blockSeconds = 3, $owner = null)
    {
        $this->blockSeconds = $blockSeconds;
        $this->lockName = $lockName;
        $this->lockingSeconds = $lockingSeconds;
        $this->owner = $owner;
    }

    /**
     * 非阻塞形式锁定.
     * @param callable $func 需锁定的闭包
     * @return mixed bool:false 获取锁失败; mixed: 闭包返回的结果
     */
    public function lockFunc(callable $func): mixed
    {
        $redisInstance = Redis::getRedisInstance();
        $lock = new \Lysice\HyperfRedisLock\RedisLock(
            $redisInstance,
            $this->lockName,
            $this->lockingSeconds,
            $this->owner ?? null
        );
        return $lock->get($func);
    }

    /**
     * 阻塞形式锁定.
     * @throws \Lysice\HyperfRedisLock\LockTimeoutException
     */
    public function lockFuncWaitTimeout(callable $func): mixed
    {
        $redisInstance = Redis::getRedisInstance();
        $lock = new \Lysice\HyperfRedisLock\RedisLock(
            $redisInstance,
            $this->lockName,
            $this->lockingSeconds,
            $this->owner ?? null
        );
        return $lock->block($this->blockSeconds, $func);
    }
}

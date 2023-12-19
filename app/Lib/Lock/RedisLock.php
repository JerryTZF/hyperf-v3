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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class RedisLock
{
    /**
     * 锁名称.
     * @var string 锁名称
     */
    private string $lockName;

    /**
     * 锁定的时间长度(该值一般应大于等于闭包执行的耗时).
     * @var int 秒数
     */
    private int $lockingSeconds;

    /**
     * 锁定的标志位(持有者).
     * @var null|mixed|string
     */
    private string $owner;

    /**
     * 等待获取锁的超时时间(阻塞上限秒数; 一般小于等于$lockingSeconds).
     * @var int 阻塞秒数
     */
    private int $blockSeconds;

    /**
     * 锁实例.
     * @var \Lysice\HyperfRedisLock\RedisLock 实例
     */
    private \Lysice\HyperfRedisLock\RedisLock $lock;

    /**
     * 实例化.
     * @param string $lockName 所名称
     * @param int $lockingSeconds 锁定秒数(超过该数值,锁会自动释放)
     * @param int $blockSeconds 阻塞秒数(超过该数值, 会抛出异常)
     * @param string $owner 锁的持有者
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    public function __construct(string $lockName, int $lockingSeconds, int $blockSeconds = 3, string $owner = '')
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
            $this->owner ?: null // 谁持有该锁
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
     * 会尝试每250ms获取一次锁, 当超过$blockSeconds秒后抛出异常.
     * @param callable $func 需锁定的闭包
     * @return mixed bool:false 获取锁失败; mixed: 闭包返回的结果
     * @throws LockTimeoutException 异常
     */
    public function lockAsync(callable $func): mixed
    {
        return $this->lock->block($this->blockSeconds, $func);
    }
}

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

namespace App\Lib\RedisQueue;

use App\Job\AbstractJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\Cache\Cache;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

class RedisQueueFactory
{
    /**
     * 根据队列名称判断是否投递消息.
     */
    public const IS_PUSH_KEY = 'IS_PUSH_%s';

    /**
     * 获取队列实例(后续准备废弃, 请使用safePush投递).
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getQueueInstance(string $queueName = 'default'): DriverInterface
    {
        return ApplicationContext::getContainer()->get(DriverFactory::class)->get($queueName);
    }

    /**
     * 根据外部变量控制是否投递消息.
     * @return mixed 是否投递成功
     * @throws InvalidArgumentException|NotFoundExceptionInterface 异常
     * @throws ContainerExceptionInterface 异常
     */
    public static function safePush(AbstractJob $job, string $queueName = 'default', int $delay = 0): bool
    {
        // 动态读取外部变量, 判断是否投递
        $key = sprintf(static::IS_PUSH_KEY, $queueName);
        $isPush = ApplicationContext::getContainer()->get(Cache::class)->get($key);
        if ($isPush !== false) {
            $queueInstance = ApplicationContext::getContainer()->get(DriverFactory::class)->get($queueName);
            return $queueInstance->push($job, $delay);
        }
        return false;
    }
}

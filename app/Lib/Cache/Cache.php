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

namespace App\Lib\Cache;

use DateInterval;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool set(string $key, mixed $value, null|int|DateInterval $ttl = null)
 * @method static bool delete(string $key)
 * @method static bool clear()
 * @method static iterable getMultiple(iterable $keys, mixed $default = null)
 * @method static bool setMultiple(iterable $values, null|int|DateInterval $ttl = null)
 * @method static bool deleteMultiple(iterable $keys)
 * @method static bool has(string $key)
 */
class Cache
{
    /**
     * 事件触发器.
     * @var eventDispatcherInterface 触发器实体类
     */
    #[Inject]
    protected EventDispatcherInterface $dispatcher;

    /**
     * 静态调用.
     * @param mixed $action 方法
     * @param mixed $args 参数
     * @return mixed 返回
     * @throws ContainerExceptionInterface 异常实体类
     * @throws NotFoundExceptionInterface 异常实体类
     */
    public static function __callStatic(mixed $action, mixed $args)
    {
        return self::getInstance()->{$action}(...$args);
    }

    /**
     * 获取实例.
     * @return CacheInterface 缓存实体类
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    public static function getInstance(): CacheInterface
    {
        return ApplicationContext::getContainer()->get(CacheInterface::class);
    }

    /**
     * 清除缓存.
     * @param string $listener 监听器
     * @param array $args 参数
     */
    public function flush(string $listener, array $args)
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent($listener, $args));
    }
}

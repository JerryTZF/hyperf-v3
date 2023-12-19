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

namespace App\Listener;

use App\Lib\Log\Log;
use Hyperf\AsyncQueue\AnnotationJob;
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\Event;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

#[Listener]
class QueueHandleListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            // 队列长度信息事件 (系统底层有监听器, 这里不再二次处理)
            // QueueLength::class,
            // 消息消费后事件
            AfterHandle::class,
            // 消息消费前事件
            BeforeHandle::class,
            // 消息消费失败事件
            FailedHandle::class,
            // 消息重试事件
            RetryHandle::class,
        ];
    }

    /**
     * 队列监听器逻辑.
     * @param object $event 消息体
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    public function process(object $event): void
    {
        if ($event instanceof Event && $event->getMessage()->job()) {
            $job = $event->getMessage()->job();
            $jobClass = get_class($job);
            if ($job instanceof AnnotationJob) {
                $jobClass = sprintf('Job[%s@%s]', $job->class, $job->method);
            }
            $date = date('Y-m-d H:i:s');

            switch (true) {
                case $event instanceof BeforeHandle:
                    Log::stdout()->info(sprintf('[%s] %s 正在消费.', $date, $jobClass));
                    break;
                case $event instanceof AfterHandle:
                    Log::stdout()->info(sprintf('[%s] %s 消费完成.', $date, $jobClass));
                    break;
                case $event instanceof FailedHandle:
                    $msg = sprintf('[%s] %s 消费失败. 异常信息: %s', $date, $jobClass, $event->getMessage());
                    Log::stdout()->error($msg);
                    Log::error($msg);
                    break;
                case $event instanceof RetryHandle:
                    $msg = sprintf('[%s] %s 正在重试.', $date, $jobClass);
                    Log::stdout()->warning($msg);
                    Log::warning($msg);
                    break;
                default:
                    Log::warning('未知事件');
            }
        }
    }
}

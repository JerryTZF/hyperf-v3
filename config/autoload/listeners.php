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
use Hyperf\AsyncQueue\Listener\QueueLengthListener;
use Hyperf\Command\Listener\FailToHandleListener;
use Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler;

return [
    // 框架提供了 error_reporting() 错误级别的监听器
    ErrorExceptionHandler::class,
    // 命令行执行异常监听器
    FailToHandleListener::class,
    // 队列长度信息监听器
    QueueLengthListener::class,
];

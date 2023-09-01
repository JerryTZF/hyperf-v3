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
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Process\Event\BeforeProcessHandle;

#[Listener]
class ConsumerProcessListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            AfterProcessHandle::class, // 系统事件(进程退出时触发)
            BeforeProcessHandle::class, // 系统事件(进程创建时触发)
        ];
    }

    public function process(object $event): void
    {
        switch (true) {
            case $event instanceof AfterProcessHandle:
                Log::stdout()->warning(sprintf('[自定义进程停止][进程:%s][第 %s 个进程]', $event->process->name, $event->index));
                break;
            case $event instanceof BeforeProcessHandle:
                Log::stdout()->info(sprintf('[自定义进程启动][进程:%s][第 %s 个进程]', $event->process->name, $event->index));
                break;
            default:
                Log::warning('未知事件');
        }
    }
}

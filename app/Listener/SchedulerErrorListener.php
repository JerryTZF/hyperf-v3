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
use Hyperf\Crontab\Event\FailToExecute;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class SchedulerErrorListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            FailToExecute::class, // 系统事件, 底层有相应的触发器触发(抛出异常会触发该事件)
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof FailToExecute) {
            $info = sprintf('任务:%s; 错误:%s', $event->crontab->getName(), $event->throwable->getMessage());
            Log::error($info);
        }
    }
}

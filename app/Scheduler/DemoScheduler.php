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

namespace App\Scheduler;

use App\Lib\Log\Log;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Throwable;

#[Crontab(
    rule: '\/10 * * * * *', // 定时任务规则
    name: 'DemoScheduler', // 定时任务名称
    singleton: true, // 并发执行只有一个被执行,例如: 很多个任务都是10:00AM执行时
    onOneServer: true, // 多实例部署项目时，则只有一个实例会被触发
    callback: 'execute', // 消费方法
    memo: '测试定时任务', // 备注
    enable: 'isEnable', // 是否启动
)]
class DemoScheduler extends AbstractScheduler
{
    // 就算不捕获异常, 底层执行也有事件触发器触发, 会被外部监听器监听到
    public function execute(): void
    {
        if (! $this->isRunning) {
            Log::stdout()->info('DemoScheduler 消费逻辑已跳出');
            return;
        }
        try {
            // TODO your crontab task.
            Log::stdout()->info(Carbon::now()->toDateTimeString());
        } catch (Throwable $e) {
            // TODO catch exception logic
            Log::stdout()->error($e->getMessage());
        } finally {
            Log::stdout()->info('DemoScheduler 执行完成');
        }
    }

    public function isEnable(): bool
    {
        return false;
    }
}

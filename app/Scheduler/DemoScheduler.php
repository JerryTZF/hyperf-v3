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

use App\Exception\SchedulerException;
use App\Lib\Log\Log;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Throwable;

#[Crontab(
    rule: '\/10 * * * * *',
    name: 'DemoScheduler',
    onOneServer: true,
    callback: 'execute',
    memo: '测试定时任务',
    enable: 'isEnable',
)]
class DemoScheduler
{
    public function execute(): void
    {
        try {
            Log::stdout()->info(Carbon::now()->toDateTimeString());
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        } finally {
            Log::stdout()->info('DemoScheduler 执行完成');
        }
    }

    public function isEnable(): bool
    {
        return \Hyperf\Support\env('APP_ENV', 'dev') === 'pro';
    }
}

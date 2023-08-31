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
        $a = 1 / 0;
        Log::stdout()->info(Carbon::now()->toDateTimeString());
    }

    public function isEnable(): bool
    {
        return \Hyperf\Support\env('APP_ENV', 'dev') === 'dev';
    }
}

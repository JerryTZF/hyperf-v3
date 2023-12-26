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

namespace App\Process;

use Hyperf\Crontab\Process\CrontabDispatcherProcess;
use Hyperf\Process\Annotation\Process;

#[Process(
    nums: 1, // 进程数目
    name: 'SchedulerProcess', // 进程名称
    redirectStdinStdout: false, // 重定向自定义进程的标准输入和输出
    pipeType: 2, // 管道类型
    enableCoroutine: true // 进程内是否启用协程
)]
class SchedulerProcess extends CrontabDispatcherProcess
{
    public string $name = 'scheduler-process';
}

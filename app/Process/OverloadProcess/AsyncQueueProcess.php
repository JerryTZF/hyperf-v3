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

namespace App\Process\OverloadProcess;

use App\Constants\ConstCode;
use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Process\Annotation\Process;

#[Process(
    nums: 4, // 消费者进程数
    name: 'AsyncQueueProcess', // 队列名称
    redirectStdinStdout: false, // 重定向自定义进程的标准输入和输出
    enableCoroutine: true, // 是否启用协程
)]
class AsyncQueueProcess extends ConsumerProcess
{
    // 这里的队列名称请和配置文件对应的队列名称保持一致
    protected string $queue = ConstCode::NORMAL_QUEUE_NAME;
}

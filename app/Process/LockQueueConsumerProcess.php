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

use App\Constants\ConstCode;
use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Process\Annotation\Process;

#[Process(
    nums: 1, // 消费者进程数
    name: 'LockQueueConsumerProcess', // 队列名称
    redirectStdinStdout: false, // 重定向自定义进程的标准输入和输出
    enableCoroutine: true, // 是否启用协程
)]
class LockQueueConsumerProcess extends ConsumerProcess
{
    protected string $queue = ConstCode::LOCK_QUEUE_NAME;
}

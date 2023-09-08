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

use App\Exception\BusinessException;
use App\Job\DemoJob;
use App\Lib\Log\Log;
use App\Lib\RedisQueue\RedisQueueFactory;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Process\ProcessManager;
use Throwable;

#[Process(
    nums: 1, // 进程数目
    name: 'PushMsgDemoProcess',
    redirectStdinStdout: false,
    pipeType: 2,
    enableCoroutine: true // 进程内是否启用协程
)]
class PushMsgDemoProcess extends AbstractProcess
{
    public function handle(): void
    {
        try {
            while (ProcessManager::isRunning()) {
                for ($i = 10; --$i;) {
                    $queueInstance = RedisQueueFactory::getQueueInstance('limit-queue');
                    $isPushOk = $queueInstance->push(new DemoJob((string) $i, []));
                    if (! $isPushOk) {
                        throw new BusinessException();
                    }
                }
                Coroutine::sleep(1000);
            }
        } catch (Throwable $e) {
            Log::stdout()->error("ConsumerProcess 异常被捕获: {$e->getMessage()}");
        } finally {
            Log::stdout()->warning('ConsumerProcess 进程将被拉起 !!!');
        }
    }

    // 是否随着服务一起启动
    public function isEnable($server): bool
    {
        return \Hyperf\Support\env('APP_ENV', 'dev') === 'pro';
    }
}

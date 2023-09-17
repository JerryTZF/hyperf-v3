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

namespace App\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Signal\Annotation\Signal;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerInterface;
use Swoole\Server;

#[Signal]
class WorkerStopHandler implements SignalHandlerInterface
{
    protected ConfigInterface $config;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        if ($signal !== SIGINT) {
            $time = $this->config->get('server.settings.max_wait_time', 3);
            Coroutine::sleep($time);
        }

        // shutdown => https://wiki.swoole.com/#/server/methods?id=shutdown 直接kill -15 不触发后续动作
        // stop => https://wiki.swoole.com/#/server/methods?id=stop 使当前 Worker 进程停止运行，并立即触发 onWorkerStop 回调函数。

        //        $this->container->get(Server::class)->shutdown();
        $this->container->get(Server::class)->stop();
    }
}

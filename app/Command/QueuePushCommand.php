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

namespace App\Command;

use App\Lib\RedisQueue\RedisQueueFactory;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * 是否允许业务向redis异步队列投递消息.
 * Class QueuePushCommand.
 */
#[Command]
class QueuePushCommand extends HyperfCommand
{
    protected const START = 'start';

    protected const STOP = 'stop';

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('queue:switch');
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp('allow or forbid push message to redis queue. eg: php bin/hyperf queue:switch redis-queue start');
        $this->setDescription('允许或禁止向指定的队列投递信息');
        $this->addArgument('queue_name', InputArgument::REQUIRED, 'redis queue name');
        $this->addArgument('action', InputArgument::OPTIONAL, 'start or stop', 'start');
    }

    public function handle()
    {
        [$config, $redis] = [
            $this->container->get(ConfigInterface::class),
            $this->container->get(Redis::class),
        ];
        [$argumentQueueName, $argumentAction] = [
            $this->input->getArgument('queue_name'),
            $this->input->getArgument('action'),
        ];
        $allRedisQueueName = array_keys($config->get('async_queue'));
        if (! in_array($argumentQueueName, $allRedisQueueName)) {
            $this->line($argumentQueueName . ' 队列未配置, 请检查', 'error');
            return null;
        }
        if (! in_array($argumentAction, ['stop', 'start'])) {
            $this->line('action 只允许 start 或 stop', 'error');
            return null;
        }

        $key = sprintf(RedisQueueFactory::IS_PUSH_KEY, $argumentQueueName);
        if ($argumentAction === self::START) {
            $redis->set($key, "{$argumentQueueName}:{$argumentAction}");
            $this->line("{$argumentQueueName} 已允许投递消息 :)", 'info');
        } else {
            $redis->del($key);
            $this->line("{$argumentQueueName} 已禁止投递消息 :)", 'info');
        }

    }
}

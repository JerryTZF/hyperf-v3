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

use App\Constants\ConstCode;
use Hyperf\Cache\Cache;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;

#[Command]
class SchedulerCommand extends HyperfCommand
{
    protected const START = 'start';

    protected const STOP = 'stop';

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('crontab:switch');
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp('make scheduler running logic or not. eg: php bin/hyperf crontab:switch start');
        $this->setDescription('定时任务是否执行消费逻辑');
        $this->addArgument('action', InputArgument::REQUIRED, 'start or stop');
    }

    public function handle()
    {
        $argumentAction = $this->input->getArgument('action');
        try {
            $cache = $this->container->get(Cache::class);
            if ($argumentAction === self::STOP) {
                $cache->delete(ConstCode::SCHEDULER_IS_RUNNING_KEY);
                $this->line('定时任务已跳出执行逻辑', 'info');
            } else {
                $cache->set(ConstCode::SCHEDULER_IS_RUNNING_KEY, 'SCHEDULER-IS-NOT-RUNNING');
                $this->line('定时任务已进入执行逻辑', 'info');
            }
        } catch (Throwable $e) {
            $this->line('发生错误: ' . $e->getMessage(), 'error');
        }
    }
}

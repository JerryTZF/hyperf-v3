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
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class FixModeCommand extends HyperfCommand
{
    protected const START = 'start';

    protected const STOP = 'stop';

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('website:switch');
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp('make website to fix mode. eg: php bin/hyperf website:switch start');
        $this->setDescription('网站是否进入维护模式');
        $this->addArgument('action', InputArgument::REQUIRED, 'start or stop');
    }

    public function handle()
    {
        $argumentAction = $this->input->getArgument('action');
        $redis = $this->container->get(Redis::class);
        if ($argumentAction === self::STOP) {
            $redis->set(ConstCode::FIX_MODE, 'WEBSITE-IS-IN-FIX-MODE');
            $this->line('网站已进入维护模式', 'info');
        } else {
            $redis->del(ConstCode::FIX_MODE);
            $this->line('网站已脱离维护模式', 'info');
        }
    }
}

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

use App\Constants\ConstCode;
use Hyperf\Cache\Cache;
use Hyperf\Context\ApplicationContext;

class AbstractScheduler
{
    // 定时任务逻辑是否执行(外部变量控制)
    protected bool $isRunning;

    public function __construct()
    {
        $cache = ApplicationContext::getContainer()->get(Cache::class);
        $isRunning = $cache->get(ConstCode::SCHEDULER_IS_RUNNING_KEY, false);
        $this->isRunning = ! ($isRunning === false);
    }
}

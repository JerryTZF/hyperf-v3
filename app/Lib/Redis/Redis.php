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
namespace App\Lib\Redis;

use Hyperf\Context\ApplicationContext;

class Redis
{
    public static function getRedis(): \Hyperf\Redis\Redis
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
    }
}

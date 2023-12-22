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

namespace App\Constants;

/**
 * 业务常量.
 * Class ConstCode.
 */
class ConstCode
{
    /**
     * OSS域名.
     */
    public const OSS_DOMAIN = 'https://img.tzf-foryou.xyz';

    /**
     * 业务普通队列名称.
     */
    public const NORMAL_QUEUE_NAME = 'redis-queue';

    /**
     * 并行消费为1的队列名称.
     */
    public const LOCK_QUEUE_NAME = 'lock-queue';

    /**
     * 默认队列名称.
     */
    public const DEFAULT_QUEUE_NAME = 'default';
}

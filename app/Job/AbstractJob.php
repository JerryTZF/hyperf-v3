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

namespace App\Job;

use Hyperf\AsyncQueue\Job;

/**
 * 异步消息体抽象类.
 * Class AbstractJob.
 */
abstract class AbstractJob extends Job
{
    /**
     * 最大尝试次数(max = $maxAttempts+1).
     * @var int 整型
     */
    public int $maxAttempts = 2;

    /**
     * 任务编号(传递编号相同任务会被覆盖!).
     * @var string ''
     */
    public string $uniqueId;

    /**
     * 消息参数.
     * @var array 关联数组
     */
    public array $params;

    public function __construct(string $uniqueId, array $params)
    {
        [$this->uniqueId, $this->params] = [$uniqueId, $params];
    }

    public function handle() {}
}

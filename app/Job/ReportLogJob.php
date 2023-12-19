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

use App\Model\LogRecords;

/**
 * 上报日志到数据库.
 * Class ReportLogJob.
 */
class ReportLogJob extends AbstractJob
{
    public function handle()
    {
        // 请勿在该消费逻辑中打印日志, 因为会无限重复调用
        (new LogRecords($this->params))->save();
    }
}

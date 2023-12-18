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

namespace App\Controller;

use App\Lib\Log\Log;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: 'test')]
class TestController extends AbstractController
{
    #[GetMapping(path: 'log')]
    public function log()
    {
//        $a = 12/0;
        Log::info('测试数据', ['a' => 'A']);

        return $this->result->getResult();
    }
}

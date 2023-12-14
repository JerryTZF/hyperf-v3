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

use Hyperf\HttpServer\Annotation\Controller;

/**
 * 三方异步回调(不需要进行权限校验).
 * Class AsyncNotifyController.
 */
#[Controller(prefix: 'async')]
class AsyncNotifyController extends AbstractController
{
}

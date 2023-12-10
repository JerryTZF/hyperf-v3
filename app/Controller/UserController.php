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

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

/**
 * 用户相关操作请求
 * Class UserController.
 */
#[Controller(prefix: 'user')]
class UserController extends AbstractController
{
    #[Inject]
    protected UserService $service;

    #[PostMapping(path: 'auth/info')]
    public function info(): array
    {
        $info = $this->service->getUserAuthInfo($this->jwtPayload['data']['uid']);
        return $this->result->setData($info)->getResult();
    }
}

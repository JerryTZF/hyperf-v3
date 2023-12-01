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
use Hyperf\HttpServer\Annotation\PostMapping;

// 自有注册、登录体系
#[Controller(prefix: 'auth')]
class LoginController extends AbstractController
{
    #[PostMapping(path: 'login')]
    public function login(): array
    {
        return $this->result->getResult();
    }

    public function logout()
    {
    }
}

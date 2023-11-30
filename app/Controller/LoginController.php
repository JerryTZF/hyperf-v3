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

use App\Lib\Jwt\Jwt;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

// 自有注册、登录体系
#[Controller(prefix: 'auth')]
class LoginController extends AbstractController
{
    public function login()
    {
    }

    public function logout()
    {
    }

    #[GetMapping(path: 'test')]
    public function demo()
    {
        $jwt = Jwt::createJwt('你好');
        $payload = Jwt::explainJwt($jwt);

        return $this->result->setData($payload)->getResult();
    }
}

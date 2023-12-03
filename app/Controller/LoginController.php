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

use App\Request\AuthRequest;
use App\Service\LoginService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;

// 自有注册、登录体系
#[Controller(prefix: 'auth')]
class LoginController extends AbstractController
{
    #[Inject]
    protected LoginService $service;

    #[PostMapping(path: 'jwt/get')]
    #[Scene(scene: 'get_jwt')]
    public function login(AuthRequest $request): array
    {
        $account = $request->input('account');
        $password = $request->input('pwd');

        return $this->result->setData($this->service->getJwt($account, $password))->getResult();
    }

    #[PostMapping(path: 'register')]
    #[Scene(scene: 'register')]
    public function register(AuthRequest $request): array
    {
        $this->service->register(...$request->inputs(['account', 'password', 'phone']));
        return $this->result->getResult();
    }

    #[PostMapping(path: 'jwt/deactivate')]
    public function logout(): array
    {
        return $this->result->getResult();
    }

    #[PostMapping(path: 'jwt/status')]
    public function loginStatus(): array
    {
        return $this->result->getResult();
    }
}

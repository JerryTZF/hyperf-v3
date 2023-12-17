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

use App\Request\LoginRequest;
use App\Service\LoginService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Validation\Annotation\Scene;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 自有注册、登录体系.
 * Class LoginController.
 */
#[Controller(prefix: 'login')]
class LoginController extends AbstractController
{
    /**
     * 登录相关服务类.
     * @var LoginService 服务类
     */
    #[Inject]
    protected LoginService $service;

    /**
     * 发送验证码.
     * @param LoginRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    #[PostMapping(path: 'send/sms')]
    #[Scene(scene: 'send_sms')]
    #[RateLimit(create: 1, consume: 1, capacity: 1)]
    public function sendSmsForRegister(LoginRequest $request): array
    {
        $phone = $request->input('phone');
        $captcha = $this->service->sendRegisterSms($phone);
        return $this->result->setData(['code' => $captcha])->getResult();
    }

    /**
     * 获取jwt && refresh jwt.
     * @param LoginRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'jwt/get')]
    #[Scene(scene: 'get_jwt')]
    public function login(LoginRequest $request): array
    {
        $account = $request->input('account');
        $password = $request->input('pwd');
        $result = $this->service->getJwt($account, $password);

        return $this->result->setData($result)->getResult();
    }

    /**
     * 注册.
     * @param LoginRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    #[PostMapping(path: 'register')]
    #[Scene(scene: 'register')]
    public function register(LoginRequest $request): array
    {
        $this->service->register(...$request->inputs(['account', 'password', 'phone', 'code']));
        return $this->result->getResult();
    }

    /**
     * 使得jwt失效.
     * @param LoginRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'jwt/deactivate')]
    #[Scene(scene: 'explain_jwt')]
    public function logout(LoginRequest $request): array
    {
        $this->service->deactivateJwt($request->input('jwt'));
        return $this->result->getResult();
    }

    /**
     * 查看jwt相关信息.
     * @param LoginRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'jwt/status')]
    #[Scene(scene: 'explain_jwt')]
    public function loginStatus(LoginRequest $request): array
    {
        $result = $this->service->explainJwt($request->input('jwt'));
        return $this->result->setData($result)->getResult();
    }

    /**刷新jwt.
     * @param LoginRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'jwt/refresh')]
    #[Scene(scene: 'refresh_jwt')]
    public function refresh(LoginRequest $request): array
    {
        $result = $this->service->refreshJwt($request->input('refresh_jwt'));
        return $this->result->setData($result)->getResult();
    }
}

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
     * 获取jwt && refresh jwt.
     * @param AuthRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'jwt/get')]
    #[Scene(scene: 'get_jwt')]
    public function login(AuthRequest $request): array
    {
        $account = $request->input('account');
        $password = $request->input('pwd');
        $result = $this->service->getJwt($account, $password);

        return $this->result->setData($result)->getResult();
    }

    /**
     * 注册.
     * @param AuthRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'register')]
    #[Scene(scene: 'register')]
    public function register(AuthRequest $request): array
    {
        $this->service->register(...$request->inputs(['account', 'password', 'phone']));
        return $this->result->getResult();
    }

    /**
     * 使得jwt失效.
     * @param AuthRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'jwt/deactivate')]
    #[Scene(scene: 'explain_jwt')]
    public function logout(AuthRequest $request): array
    {
        $this->service->deactivateJwt($request->input('jwt'));
        return $this->result->getResult();
    }

    /**
     * 查看jwt相关信息.
     * @param AuthRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'jwt/status')]
    #[Scene(scene: 'explain_jwt')]
    public function loginStatus(AuthRequest $request): array
    {
        $result = $this->service->explainJwt($request->input('jwt'));
        return $this->result->setData($result)->getResult();
    }

    /**刷新jwt.
     * @param AuthRequest $request 请求验证类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'jwt/refresh')]
    #[Scene(scene: 'explain_jwt')]
    public function refresh(AuthRequest $request): array
    {
        $result = $this->service->refreshJwt($request->input('jwt'));
        return $this->result->setData($result)->getResult();
    }
}

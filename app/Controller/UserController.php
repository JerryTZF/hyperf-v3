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

use App\Request\UserRequest;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;

/**
 * 用户相关操作请求
 * Class UserController.
 */
#[Controller(prefix: 'user')]
class UserController extends AbstractController
{
    #[Inject]
    protected UserService $service;

    /**
     * 用户权限信息.
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[GetMapping(path: 'auth/info')]
    public function info(): array
    {
        $info = $this->service->getUserAuthInfo($this->jwtPayload['data']['uid']);
        return $this->result->setData($info)->getResult();
    }

    /**
     * 修改密码.
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'update/password')]
    #[Scene(scene: 'update_password')]
    public function updatePassword(UserRequest $request): array
    {
        $password = $request->input('password');
        $uid = $this->jwtPayload['data']['uid'];

        $this->service->updatePassword($uid, $password);
        return $this->result->getResult();
    }

    /**
     * 修改基本信息(包含状态).
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'update/info')]
    #[Scene(scene: 'update_info')]
    public function updateInfo(UserRequest $request): array
    {
        $post = $request->post();
        $uid = $this->jwtPayload['data']['uid'];
        $this->service->updateBasicInfo($uid, $post);
        return $this->result->getResult();
    }

    /**
     * 为用户绑定角色.
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'bind/role')]
    #[Scene(scene: 'bind')]
    public function bind(UserRequest $request): array
    {
        $roleIds = $request->input('role_id');
        $uid = $this->jwtPayload['data']['uid'];

        $this->service->bindRole($uid, $roleIds);
        return $this->result->getResult();
    }
}

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

use App\Request\RoleRequest;
use App\Service\RoleService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;

/**
 * 角色相关操作控制器
 * Class RoleController.
 */
#[Controller(prefix: 'role')]
class RoleController extends AbstractController
{
    #[Inject]
    protected RoleService $service;

    /**
     * 获取角色列表.
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[GetMapping(path: 'list')]
    public function getRoleList(): array
    {
        $roleName = $this->request->input('role_name');
        return $this->result->setData($this->service->list($roleName))->getResult();
    }

    /**
     * 添加角色.
     * @param RoleRequest $request 验证请求类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'add')]
    #[Scene(scene: 'add')]
    public function addRole(RoleRequest $request): array
    {
        $this->service->add($request->input('role_name'));
        return $this->result->getResult();
    }

    /**
     * 绑定权限至指定角色.
     * @param RoleRequest $request 验证请求类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'bind')]
    #[Scene(scene: 'bind')]
    public function bind(RoleRequest $request): array
    {
        $this->service->bind($request->input('role_id'), $request->input('auth_id'));
        return $this->result->getResult();
    }

    /**修改角色名称.
     * @param RoleRequest $request 验证请求类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'name/update')]
    #[Scene(scene: 'update_name')]
    public function updateRoleName(RoleRequest $request): array
    {
        $rid = $request->input('role_id');
        $roleName = $request->input('role_name');

        $this->service->updateInfo($rid, [
            'role_name' => $roleName,
        ]);
        return $this->result->getResult();
    }

    /**
     * 修改角色状态.
     * @param RoleRequest $request 验证请求类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'status/update')]
    #[Scene(scene: 'update_status')]
    public function updateStatus(RoleRequest $request): array
    {
        $rid = $request->input('role_id');
        $status = $request->input('status');

        $this->service->updateInfo($rid, [
            'status' => $status,
        ]);
        return $this->result->getResult();
    }
}

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

    /**
     * 修改角色信息(非权限节点).
     */
    #[PostMapping(path: 'update')]
    #[Scene(scene: 'update')]
    public function update(RoleRequest $request): array
    {
        $rid = $request->input('role_id');
        $roleName = $request->input('role_name');
        $status = $request->input('status');
        $update = [];

        $roleName !== null && $update['role_name'] = $roleName;
        $status !== null && $update['status'] = $status;
        $this->service->updateInfo($rid, $update);
        return $this->result->getResult();
    }
}

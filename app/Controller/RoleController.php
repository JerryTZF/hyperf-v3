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
}

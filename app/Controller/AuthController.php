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
use App\Service\AuthService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 权限节点操作相关控制器.
 * Class AuthController.
 */
#[Controller(prefix: 'auth')]
class AuthController extends AbstractController
{
    #[Inject]
    protected AuthService $service;

    #[PostMapping(path: 'myself/info')]
    public function getSelfAuthorityInfo(): array
    {
        $jwt = $this->jwtPayload;
        var_dump($jwt);
        return $this->result->getResult();
    }

    /**
     * 权限节点归属于的角色.
     * @param AuthRequest $request 验证请求类
     * @return array []
     */
    #[PostMapping(path: 'belong/roles')]
    #[Scene(scene: 'belong')]
    public function authBelongRole(AuthRequest $request): array
    {
        $route = $request->input('route');
        $aid = $request->input('auth_id');
        return $this->result->setData($this->service->belongRoles($aid, $route))->getResult();
    }

    /**
     * 修改权限节点状态.
     * @param AuthRequest $request 验证请求类
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'status/update')]
    #[Scene(scene: 'update')]
    public function addRole(AuthRequest $request): array
    {
        $aid = $request->input('auth_id');
        $status = $request->input('status');
        $this->service->updateAuthStatus($aid, $status);
        return $this->result->getResult();
    }

    /**
     * 获取权限节点.
     * @return array []
     */
    #[GetMapping(path: 'list')]
    public function getAuthsList(): array
    {
        return $this->result->setData($this->service->getAuthsInfoWithDB())->getResult();
    }

    /**
     * 同步API路由节点信息.
     * @return array []
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    #[GetMapping(path: 'sync/list')]
    public function getAuthsListAndUpdate(): array
    {
        return $this->result->setData($this->service->getAuthsInfoWithoutDB())->getResult();
    }
}

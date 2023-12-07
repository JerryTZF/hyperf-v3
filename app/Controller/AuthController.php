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

use App\Model\Auths;
use App\Service\AuthService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 权限操作相关控制器.
 * Class AuthController.
 */
#[Controller(prefix: 'auth')]
class AuthController extends AbstractController
{
    #[Inject]
    protected AuthService $service;

    #[PostMapping(path: 'myself/info')]
    public function getSelfAuthorityInfo()
    {
    }

    #[PostMapping(path: 'role/add')]
    public function addRole(): array
    {
        return $this->result->getResult();
    }

    /**
     * 获取权限节点.
     * @return array []
     */
    #[PostMapping(path: 'auth/list')]
    public function getAuthsList(): array
    {
        return $this->result->getResult();
    }

    /**
     * 同步API路由节点信息.
     * @return array []
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    #[PostMapping(path: 'auth/sync')]
    public function syncAuthsTable(): array
    {
        $routesInfo = $this->service->getRoutesInfoWithoutDB();
        Auths::truncate();
        Auths::insert($routesInfo);

        return $this->result->setData($routesInfo)->getResult();
    }
}

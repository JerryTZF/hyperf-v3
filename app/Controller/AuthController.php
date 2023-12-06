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

use App\Service\AuthService;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Router\DispatcherFactory;

/**
 * 权限操作相关控制器.
 * Class AuthController.
 */
#[Controller(prefix: 'authority')]
class AuthController extends AbstractController
{
    #[Inject]
    protected AuthService $service;

    #[PostMapping(path: 'myself/info')]
    public function getSelfAuthorityInfo() {}

    #[PostMapping(path: 'role/add')]
    public function addRole(): array
    {
        return $this->result->getResult();
    }

    #[PostMapping(path: 'auth/add')]
    public function addAuth(): array
    {
        $routes = $this->service->getAllRoutesInfo();
        return $this->result->addKey('routes', $routes)->getResult();
    }
}

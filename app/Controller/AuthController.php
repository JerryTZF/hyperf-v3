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
use Carbon\Carbon;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;

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
    public function getSelfAuthorityInfo()
    {
    }

    #[PostMapping(path: 'role/add')]
    public function addRole(): array
    {
        return $this->result->getResult();
    }

    #[PostMapping(path: 'auth/sync')]
    public function syncAuthsTable(): array
    {
        $factory = ApplicationContext::getContainer()->get(DispatcherFactory::class);
        $routes = Arr::first($factory->getRouter('http')->getData(), function ($v, $k) {return ! empty($v); });
        $nowDate = Carbon::now()->toDateTimeString();
        foreach ($routes as $method => $value) {
            /** @var Handler $info */
            foreach ($value as $info) {
                [$callback, $route] = [$info->callback, $info->route];
                $where = [
                    'method' => $method,
                    'route' => $route,
                    'controller' => $callback[0],
                    'function' => $callback[1],
                ];
                Auths::firstOrCreate($where, ['create_time' => $nowDate, 'update_time' => $nowDate]);
            }
        }

        return $this->result->getResult();
    }
}

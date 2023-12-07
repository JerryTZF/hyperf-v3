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

namespace App\Service;

use App\Model\Auths;
use Carbon\Carbon;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AuthService extends AbstractService
{
    public function getAuthsByUid(int $uid) {}

    /**
     * 获取全局路由.
     * @return array 关联数组
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getRoutesInfoWithoutDB(): array
    {
        $factory = ApplicationContext::getContainer()->get(DispatcherFactory::class);
        $routes = Arr::first($factory->getRouter('http')->getData(), function ($v, $k) {return ! empty($v); });
        $formatRoutes = [];
        $nowDate = Carbon::now()->toDateTimeString();
        foreach ($routes as $method => $value) {
            /** @var Handler $info */
            foreach ($value as $info) {
                [$callback, $route] = [$info->callback, $info->route];
                $formatRoutes[] = [
                    'method' => $method,
                    'route' => $route,
                    'controller' => $callback[0],
                    'function' => $callback[1],
                    'status' => Auths::STATUS_ACTIVE,
                    'create_time' => $nowDate,
                    'update_time' => $nowDate,
                ];
            }
        }

        return $formatRoutes;
    }
}

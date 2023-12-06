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

use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Router\DispatcherFactory;
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
    public function getAllRoutesInfo(): array
    {
        $factory = ApplicationContext::getContainer()->get(DispatcherFactory::class);
        $routes = $factory->getRouter('http')->getData();
        return Arr::first($routes, function ($v, $k) {
            return ! empty($v);
        });
    }
}

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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Auths;
use App\Model\Roles;
use Carbon\Carbon;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AuthService extends AbstractService
{
    public function getUserAuths(int $uid): array
    {
        return [];
    }

    /**
     * 当前权限归属于哪些角色.
     * @param null|int $aid 权限ID
     * @param null|string $route 路由
     * @return array []
     */
    public function belongRoles(?int $aid, ?string $route): array
    {
        $aid = ! is_null($aid) ? $aid : Auths::query()->where(['route' => $route])->value('id');
        $roles = Roles::query()
            ->where(['status' => Roles::STATUS_ACTIVE])
            ->where('auth_id', 'like', "%{$aid}%")
            ->orWhere(['super_admin' => Roles::IS_SUPER_ADMIN])
            ->pluck('role_name', 'id')
            ->toArray();
        if (empty($roles)) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::ROLE_EMPTY));
        }

        return $roles;
    }

    /**
     * 变更权限节点状态.
     * @param int $id 权限id
     * @param string $status 权限状态
     * @return bool 变更是否成功
     */
    public function updateAuthStatus(int $id, string $status): bool
    {
        $effectRows = Auths::query()->where(['id' => $id])->update(['status' => $status]);
        return $effectRows > 0;
    }

    /**
     * 获取全局路由.
     * @return array [][]
     */
    public function getAuthsInfoWithDB(): array
    {
        $fields = ['id', 'method', 'route', 'function'];
        return Auths::query()->select($fields)->where(['status' => Auths::STATUS_ACTIVE])->get()->toArray();
    }

    /**
     * 获取全局路由(会重新同步路由).
     * @return array 关联数组
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAuthsInfoWithoutDB(): array
    {
        $factory = ApplicationContext::getContainer()->get(DispatcherFactory::class);
        $routes = Arr::first($factory->getRouter('http')->getData(), function ($v, $k) {return ! empty($v); });
        $list = [];
        $nowDate = Carbon::now()->toDateTimeString();
        Auths::truncate();
        foreach ($routes as $method => $value) {
            /** @var Handler $info */
            foreach ($value as $info) {
                [$callback, $route] = [$info->callback, $info->route];
                $insert = [
                    'method' => $method,
                    'route' => $route,
                    'controller' => $callback[0],
                    'function' => $callback[1],
                    'create_time' => $nowDate,
                    'update_time' => $nowDate,
                ];
                $aid = Auths::insertGetId($insert);
                unset($insert['controller'], $insert['create_time'], $insert['update_time']);
                $insert['id'] = $aid;
                $list[] = $insert;
            }
        }

        return $list;
    }
}

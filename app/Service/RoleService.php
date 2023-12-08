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
use App\Model\Roles;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use JetBrains\PhpStorm\ArrayShape;

class RoleService extends AbstractService
{
    /**
     * 默认角色的名字.
     * @var string 字符串
     */
    protected string $defaultRoleName = '默认角色';

    /**
     * 获取默认角色的角色ID.
     * @return int 角色ID
     */
    public function getDefaultRoleId(): int
    {
        return Roles::query()->where(['status' => Roles::STATUS_ACTIVE])
            ->where(function (Builder $query) {
                $roleName = $this->defaultRoleName;
                $query->whereRaw("auth_id='' OR node_id='' OR role_name='{$roleName}'");
            })->value('id');
    }

    /**
     * 添加角色(默认没有任何权限).
     * @param string $roleName 角色名称
     */
    public function add(string $roleName): void
    {
        $now = Carbon::now()->toDateTimeString();
        Roles::firstOrCreate(['role_name' => $roleName], ['create_time' => $now, 'update_time' => $now]);
    }

    /**
     * 获取角色对应的权限列表.
     * @param int $rid 角色id
     * @return array 权限节点
     */
    #[ArrayShape(['auth_list' => 'mixed[]', 'node_list' => 'array'])]
    public function getAuthsByRoleId(int $rid): array
    {
        $authIds = Roles::query()
            ->where(['id' => $rid, 'status' => Roles::STATUS_ACTIVE])
            ->value('auth_id');
        $authList = Auths::query()->whereIn('id', $authIds)
            ->select(['id', 'method', 'route', 'function'])
            ->get()
            ->toArray();
        $nodeList = []; // TODO
        return ['auth_list' => $authList, 'node_list' => $nodeList];
    }
}

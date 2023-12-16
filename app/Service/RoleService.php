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
use JetBrains\PhpStorm\ArrayShape;

class RoleService extends AbstractService
{
    /**
     * 默认角色的名字.
     * @var string 字符串
     */
    protected string $defaultRoleName = '默认角色';

    /**
     * 获取角色列表.
     * @param null|string $roleName 角色名称
     * @return array 角色列表
     */
    public function list(?string $roleName): array
    {
        $fields = ['id', 'role_name', 'super_admin', 'auth_id', 'node_id'];
        return Roles::query()->select($fields)
            ->where(['status' => Roles::STATUS_ACTIVE])
            ->when($roleName, function ($query, $roleName) {
                $query->where('role_name', 'like', "%{$roleName}%");
            })->get()->toArray();
    }

    /**
     * 绑定权限节点到指定角色.
     * @param int $rid 角色ID
     * @param array|int $aid 权限ID或权限ID集合
     * @throws BusinessException 异常
     */
    public function bind(int $rid, array|int $aid): void
    {
        $newAuthIds = is_array($aid) ? array_map('intval', array_unique($aid)) : [$aid];
        /** @var Roles $roleInfo */
        $roleInfo = Roles::query()->where(['id' => $rid, 'status' => Roles::STATUS_ACTIVE])->first();
        // 超级管理员无需添加权限节点(永远拥有所有权限节点)
        if ($roleInfo->super_admin === Roles::IS_SUPER_ADMIN) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::SUPER_ADMIN));
        }
        // 权限ID集合中是否有并不存在的权限
        $authsInfo = Auths::query()->whereIn('id', $newAuthIds)->get();
        if ($authsInfo->count() !== count($newAuthIds) || $roleInfo === null) {
            $errorCode = is_null($roleInfo) ? ErrorCode::ROLE_EMPTY : ErrorCode::AUTH_NOT_FOUND;
            throw new BusinessException(...self::getErrorMap($errorCode));
        }

        $oldAuthIds = $roleInfo->auth_id; // 注意模型修改器
        if (empty($oldAuthIds)) {
            $newStringAuthIds = trim(implode(',', $newAuthIds));
        } else {
            $newStringAuthIds = trim(implode(',', array_unique(array_merge($oldAuthIds, $newAuthIds))));
        }

        $roleInfo->auth_id = $newStringAuthIds;
        $roleInfo->save();
    }

    /**
     * 修改角色基本信息.
     * @param int $rid 角色ID
     * @param array $update 要修改的字段(关联数组)
     */
    public function updateInfo(int $rid, array $update = []): void
    {
        /** @var Roles $roleInfo */
        $roleInfo = Roles::query()->where(['id' => $rid])->first();
        if ($roleInfo === null) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::ROLE_EMPTY));
        }

        // 默认角色不允许被修改
        if ($roleInfo->role_name === $this->defaultRoleName) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::DEFAULT_ROLE_PROTECT));
        }
        // 超级管理员不允许禁用
        $isBan = isset($update['status']) && ($update['status'] === Roles::STATUS_PAUSE || $update['status'] === Roles::STATUS_DELETE);
        if ($roleInfo->super_admin === Roles::IS_SUPER_ADMIN && $isBan) {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::SUPER_ADMIN, message: '超级管理不能禁用或暂停'));
        }

        isset($update['status']) && in_array($update['status'], Roles::STATUS_ARR) && $roleInfo->status = $update['status'];
        isset($update['role_name']) && $roleInfo->role_name = $update['role_name'];
        $roleInfo->save();
    }

    /**
     * 获取默认角色的角色ID.
     * @return int 角色ID
     */
    public function getDefaultRoleId(): int
    {
        return Roles::query()
            ->where(['status' => Roles::STATUS_ACTIVE, 'role_name' => $this->defaultRoleName])
            ->value('id') ?? 0;
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
     * 获取多个或者单个角色对应的权限列表.
     * @param array|int $rids 角色IDs
     * @return array 权限节点
     */
    #[ArrayShape(['auth_list' => 'array|mixed[]', 'node_list' => 'array'])]
    public function getAuthsByRoleIds(array|int $rids): array
    {
        $authFields = ['id', 'method', 'route', 'function'];
        $rids = array_map('intval', array_unique(is_int($rids) ? [$rids] : $rids));
        [$authList, $nodeList] = [[], []];
        $rolesList = Roles::query()->whereIn('id', $rids)->where(['status' => Roles::STATUS_ACTIVE])->get();
        /** @var Roles $role */
        foreach ($rolesList as $role) {
            if ($role->super_admin === Roles::IS_SUPER_ADMIN) {
                $authList = Auths::query()->where(['status' => Auths::STATUS_ACTIVE])->select($authFields)->get()->toArray();
                break;
            }
            // 该角色对应的权限节点信息
            $thisRoleWithAuthFields = Auths::query()->whereIn('id', $role->auth_id)->select($authFields)->get()->toArray();
            // 权限节点列表
            $authList = array_merge($authList, $thisRoleWithAuthFields);
        }

        $authList = array_unique($authList, SORT_REGULAR);
        return ['auth_list' => $authList, 'node_list' => $nodeList];
    }

    /**
     * 角色中是否是超级管理员.
     * @param int $rid 角色ID
     * @return bool 是否是超管
     */
    public function isSuperAdmin(int $rid): bool
    {
        /** @var Roles $roleInfo */
        $roleInfo = Roles::query()->select(['super_admin'])->where(['id' => $rid])->first();
        if ($roleInfo === null) {
            return false;
        }
        return $roleInfo->super_admin === Roles::IS_SUPER_ADMIN;
    }
}

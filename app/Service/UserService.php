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
use App\Model\Users;
use Hyperf\Di\Annotation\Inject;

class UserService extends AbstractService
{
    #[Inject]
    protected RoleService $roleService;

    /**
     * 修改密码.
     * @param int $uid 用户ID
     * @param string $newPassword 密码
     */
    public function updatePassword(int $uid, string $newPassword): void
    {
        $userInfo = $this->getUserInfoIfExist($uid);
        // 密码未变更
        if ($userInfo->password === md5($newPassword)) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::PWD_SAME));
        }
        $userInfo->password = md5($newPassword);
        $userInfo->jwt_token = '';
        $userInfo->refresh_jwt_token = '';
        $userInfo->save();
    }

    /**
     * 修改用户基本信息.
     * @param int $uid 用户ID
     * @param array $infos 基本信息
     */
    public function updateBasicInfo(int $uid, array $infos = []): void
    {
        $userInfo = $this->getUserInfoIfExist($uid);
        // 超级管理员不允许被禁用
        if (isset($infos['status']) && $infos['status'] === Users::STATUS_BAN) {
            foreach ($userInfo->role_id as $rid) {
                if ($this->roleService->isSuperAdmin(intval($rid))) {
                    throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::SUPER_ADMIN, message: '超级管理不能禁用或暂停'));
                }
            }
        }

        isset($infos['phone']) && $userInfo->phone = $infos['phone'];
        isset($infos['sex']) && $userInfo->sex = $infos['sex'];
        isset($infos['status']) && $userInfo->status = $infos['status'];
        isset($infos['age']) && $userInfo->age = $infos['age'];
        $userInfo->save();
    }

    /**
     * 获取用户角色、权限信息.
     * @param int $uid 用户ID
     * @return array [][] eg: ['role_name'=>[['id'=>1,'method'=>'POST','route'=>'/x/x/x','function'=>'xxx'],[],[]]]
     */
    public function getUserAuthInfo(int $uid): array
    {
        $userInfo = $this->getUserInfoIfExist($uid);
        $rolesList = Roles::query()->whereIn('id', $userInfo->role_id)->where(['status' => Roles::STATUS_ACTIVE])->get();
        $authFields = ['id', 'method', 'route', 'function'];
        $result = [];
        /** @var Roles $role */
        foreach ($rolesList as $role) {
            $result[$role->role_name] = $role->super_admin === Roles::IS_SUPER_ADMIN ?
                Auths::query()->where(['status' => Auths::STATUS_ACTIVE])->select($authFields)->get()->toArray() :
                Auths::query()->whereIn('id', $role->auth_id)->select($authFields)->get()->toArray();
        }

        return $result;
    }

    /**
     * 用户绑定角色.
     * @param int $uid 用户ID
     * @param array|int $roleIds 角色IDs
     */
    public function bindRole(int $uid, array|int $roleIds): void
    {
        $userInfo = $this->getUserInfoIfExist($uid);
        $roleIds = is_array($roleIds) ? array_map('intval', array_unique($roleIds)) : [$roleIds];
        // 角色IDs中是否含有不存在的角色
        $rolesInfo = Roles::query()->whereIn('id', $roleIds)->get();
        if ($rolesInfo->count() !== count($roleIds)) {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::ROLE_EMPTY, message: '部分角色不存在'));
        }
        $oldRoleIds = $userInfo->role_id;
        if (empty($oldRoleIds)) {
            $newStringRoleIds = trim(implode(',', $roleIds));
        } else {
            // 超管无需再次绑定其他角色
            foreach ($oldRoleIds as $rid) {
                if ($this->roleService->isSuperAdmin(intval($rid))) {
                    throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::SUPER_ADMIN, message: '超管无需再次绑定其他角色'));
                }
            }

            $newStringRoleIds = trim(implode(',', array_unique(array_merge($oldRoleIds, $roleIds))));
        }

        $userInfo->role_id = $newStringRoleIds;
        $userInfo->save();
    }

    /**
     * 获取用户信息.
     * @param int $uid 用户ID
     * @return Users 用户对象
     * @throws BusinessException 异常
     */
    private function getUserInfoIfExist(int $uid): Users
    {
        /** @var Users $userInfo */
        $userInfo = Users::query()->where(['id' => $uid])->first();
        // 用户不存在
        if ($userInfo === null) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::USER_NONE));
        }
        return $userInfo;
    }
}

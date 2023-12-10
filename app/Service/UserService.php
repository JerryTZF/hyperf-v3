<?php

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Auths;
use App\Model\Roles;
use App\Model\Users;

class UserService extends AbstractService
{
    /**
     * 获取用户角色、权限信息
     * @param int $uid 用户ID
     * @return array [][] eg: ['role_name'=>[['id'=>1,'method'=>'POST','route'=>'/x/x/x','function'=>'xxx'],[],[]]]
     */
    public function getUserAuthInfo(int $uid): array
    {
        /** @var Users $userInfo */
        $userInfo = Users::query()->where(['id' => $uid])->first();
        if ($userInfo === null) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::USER_NONE));
        }

        $rolesInfo = Roles::query()->whereIn('id', $userInfo->role_id)->where(['status' => Roles::STATUS_ACTIVE])->get();
        $authFields = ['id', 'method', 'route', 'function'];
        $result = [];
        /** @var Roles $role */
        foreach ($rolesInfo as $role) {
            if ($role->super_admin === Roles::IS_SUPER_ADMIN) {
                $result[$role->role_name] = Auths::query()->where(['status' => Auths::STATUS_ACTIVE])->select($authFields)->get()->toArray();
                continue;
            }
            $result[$role->role_name] = Auths::query()->whereIn('id', $role->auth_id)->select($authFields)->get()->toArray();
        }

        return $result;
    }
}
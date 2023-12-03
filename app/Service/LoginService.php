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
use App\Lib\Jwt\Jwt;
use App\Model\Users;

class LoginService
{
    /**
     * 获取JWT.
     */
    public function getJwt(string $account, string $password): string
    {
        /** @var Users $userInfo */
        $userInfo = Users::query()->where(['account' => $account, 'password' => md5($password)])->first();
        if ($userInfo === null) {
            throw new BusinessException(
                ErrorCode::USER_NOT_FOUND,
                ErrorCode::getMessage(ErrorCode::USER_NOT_FOUND, ["{$account}"])
            );
        }
        $jwt = Jwt::createJwt($userInfo->toArray());
        $userInfo->jwt_token = $jwt;
        $userInfo->save();

        return $jwt;
    }

    /**
     * 注册.
     */
    public function register(string $account, string $password, string $phone): void
    {
        $exist = Users::query()
            ->where(['phone' => $phone])
            ->orWhere(['account' => $account])
            ->exists();
        if ($exist) {
            throw new BusinessException(
                ErrorCode::USER_HAD_REGISTERED,
                ErrorCode::getMessage(ErrorCode::USER_HAD_REGISTERED, ["{$phone} 或者 {$account}"])
            );
        }

        (new Users([
            'account' => $account,
            'password' => md5($password),
            'phone' => $phone,
        ]))->save();
    }
}
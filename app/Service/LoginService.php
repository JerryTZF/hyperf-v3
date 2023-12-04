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
use App\Lib\Lock\RedisLock;
use App\Model\Users;
use Carbon\Carbon;
use JetBrains\PhpStorm\ArrayShape;

class LoginService extends AbstractService
{
    /**
     * 获取JWT.
     */
    #[ArrayShape(['jwt' => 'string', 'refresh_jwt' => 'string'])]
    public function getJwt(string $account, string $password): array
    {
        /** @var Users $userInfo */
        $userInfo = Users::query()->where(['account' => $account, 'password' => md5($password)])->first();
        if ($userInfo === null) {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::USER_NOT_FOUND, opt: ["{$account}"]));
        }
        $jwt = Jwt::createJwt([
            'uid' => $userInfo->id,
            'rid' => $userInfo->role_id,
        ], Carbon::now()->addSeconds(2 * 60 * 60)->timestamp);
        $refreshJwt = Jwt::createJwt($userInfo->id, Carbon::now()->addDays(7)->timestamp);
        $userInfo->jwt_token = $jwt;
        $userInfo->refresh_jwt_token = $refreshJwt;
        $userInfo->save();

        return ['jwt' => $jwt, 'refresh_jwt' => $refreshJwt];
    }

    /**
     * 注册.
     */
    public function register(string $account, string $password, string $phone): void
    {
        $lock = new RedisLock('register', 3, 1, $account);
        $registerResult = $lock->lockSync(function () use ($account, $password, $phone) {
            $exist = Users::query()->where(['phone' => $phone])->orWhere(['account' => $account])->exists();
            if ($exist) {
                return ['msg' => "{$account} had registered"];
            }
            $isSaved = (new Users([
                'account' => $account,
                'password' => md5($password),
                'phone' => $phone,
            ]))->save();
            return $isSaved ? ['msg' => 'ok'] : ['msg' => 'save fail'];
        });
        // 获取锁失败
        if ($registerResult === false) {
            throw new BusinessException(...self::getErrorMap(ErrorCode::ACT_BUSY));
        }
        // 注册失败
        if (isset($registerResult['msg']) && $registerResult['msg'] !== 'ok') {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::USER_HAD_REGISTERED, opt: ["{$account} 或者 {$phone}"]));
        }
    }

    /**
     * 使用户存储的jwt_token失效.
     */
    public function deactivateJwt(string $jwt): void
    {
        $originalData = Jwt::explainJwt($jwt);
        /** @var Users $userInfo */
        $userInfo = Users::query()->where(['id' => $originalData['data']['uid'], 'jwt_token' => $jwt])->first();
        if ($userInfo === null) {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::USER_NOT_FOUND, message: '未知用户的jwt'));
        }
        $userInfo->jwt_token = '';
        $userInfo->refresh_jwt_token = '';
        $userInfo->save();
    }

    /**
     * 解析jwt.
     */
    #[ArrayShape(['exp' => 'int|mixed', 'uid' => 'mixed', 'iat' => 'mixed'])]
    public function explainJwt(string $jwt): array
    {
        $originalData = Jwt::explainJwt($jwt);
        $userInfo = Users::query()->where(['id' => $originalData['data']['uid'], 'jwt_token' => $jwt])->first();
        if ($userInfo === null) {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::USER_NOT_FOUND, message: '该 jwt 已失效'));
        }
        $exp = $originalData['exp'] - time() > 0 ? $originalData['exp'] - time() : 0;
        return [
            'exp' => $exp, // 剩余秒数
            'uid' => $originalData['data']['uid'], // uid
            'iat' => Carbon::createFromTimestamp($originalData['iat'])->toDateTimeString(), // 颁发时间
        ];
    }
}

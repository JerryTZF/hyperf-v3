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
use Hyperf\Di\Annotation\Inject;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class LoginService extends AbstractService
{
    #[Inject]
    protected RoleService $roleService;

    /**
     * 获取JWT.
     * @param string $account 账号
     * @param string $password 密码
     * @return array ['jwt' => 'string', 'refresh_jwt' => 'string']
     */
    #[ArrayShape(['jwt' => 'string', 'refresh_jwt' => 'string'])]
    public function getJwt(string $account, string $password): array
    {
        /** @var Users $userInfo */
        $userInfo = Users::query()
            ->where(['account' => $account, 'password' => md5($password), 'status' => Users::STATUS_ACTIVE])
            ->select(['id', 'role_id', 'jwt_token', 'refresh_jwt_token'])
            ->first();
        if ($userInfo === null) {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::USER_NOT_FOUND, opt: ["{$account}"]));
        }

        // payload 为什么只存储 uid(用户ID) 和 rid(角色IDs) ?
        // 1、payload 尽量不放置敏感信息, 权限节点信息属于敏感信息;
        // 2、payload 不应该过大, 应该防止可用于查询的简单信息.
        $data = [
            'uid' => $userInfo->id,
            'rid' => $userInfo->role_id,
        ];
        $jwt = Jwt::createJwt($data, Carbon::now()->addDays()->timestamp);
        $refreshJwt = Jwt::createJwt($data, Carbon::now()->addDays(7)->timestamp);
        $userInfo->jwt_token = $jwt;
        $userInfo->refresh_jwt_token = $refreshJwt;
        $userInfo->save();

        return ['jwt' => $jwt, 'refresh_jwt' => $refreshJwt];
    }

    /**
     * 注册.
     * @param string $account 账号
     * @param string $password 密码
     * @param string $phone 手机号
     */
    public function register(string $account, string $password, string $phone): void
    {
        $lock = new RedisLock('register', 3, 1, $account);
        // query & insert. so locked.
        $registerResult = $lock->lockSync(function () use ($account, $password, $phone) {
            try {
                $exist = Users::query()->where(['phone' => $phone])->orWhere(['account' => $account])->exists();
                if ($exist) {
                    return ['msg' => "{$account} had registered"];
                }
                $defaultRoleId = $this->roleService->getDefaultRoleId();
                $isSaved = (new Users([
                    'account' => $account,
                    'password' => md5($password),
                    'phone' => $phone,
                    'role_id' => (string) $defaultRoleId,
                ]))->save();
            } catch (Throwable) {
                $isSaved = false;
            }

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
     * @param string $jwt jwt
     */
    public function deactivateJwt(string $jwt): void
    {
        $originalData = Jwt::explainJwt($jwt);
        /** @var Users $userInfo */
        $userInfo = Users::query()
            ->where(['id' => $originalData['data']['uid'], 'jwt_token' => $jwt, 'status' => Users::STATUS_ACTIVE])
            ->select(['jwt_token', 'refresh_jwt_token'])
            ->first();
        if ($userInfo === null) {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::USER_NOT_FOUND, message: '未知用户的jwt'));
        }
        $userInfo->jwt_token = '';
        $userInfo->refresh_jwt_token = '';
        $userInfo->save();
    }

    /**
     * 解析jwt.
     * @param string $jwt jwt
     * @return array ['exp' => "int|mixed", 'uid' => "int|mixed", 'jwt_data' => "mixed", 'exp_date' => "string", 'iat_date' => "string"]
     */
    #[ArrayShape(['exp' => 'int|mixed', 'uid' => 'int|mixed', 'jwt_data' => 'mixed', 'exp_date' => 'string', 'iat_date' => 'string'])]
    public function explainJwt(string $jwt): array
    {
        $originalData = Jwt::explainJwt($jwt);
        $uid = $originalData['data']['uid'] ?? 0;
        $exp = $originalData['exp'] - time() > 0 ? $originalData['exp'] - time() : 0;
        return [
            'exp' => $exp, // 剩余秒数
            'uid' => $uid, // uid
            'jwt_data' => $originalData['data'], // data
            'exp_date' => Carbon::createFromTimestamp($originalData['exp'])->toDateTimeString(), // 失效时间
            'iat_date' => Carbon::createFromTimestamp($originalData['iat'])->toDateTimeString(), // 颁发时间
        ];
    }

    /**
     * 刷新jwt.
     * @param string $refreshJwt refresh_jwt
     * @return string new jwt
     */
    public function refreshJwt(string $refreshJwt): string
    {
        $originalData = Jwt::explainJwt($refreshJwt);
        $id = $originalData['data']['uid'] ?? 0;
        /** @var Users $userInfo */
        $userInfo = Users::query()
            ->where(['id' => $id, 'refresh_jwt_token' => $refreshJwt, 'status' => Users::STATUS_ACTIVE])
            ->select(['id', 'role_id', 'jwt_token'])
            ->first();
        if ($userInfo === null) {
            throw new BusinessException(...self::getErrorMap(errorCode: ErrorCode::USER_NOT_FOUND, message: '未知的 refresh jwt'));
        }

        $jwt = Jwt::createJwt([
            'uid' => $userInfo->id,
            'rid' => $userInfo->role_id,
        ], Carbon::now()->addDays()->timestamp);
        $userInfo->jwt_token = $jwt;
        $userInfo->save();

        return $jwt;
    }
}

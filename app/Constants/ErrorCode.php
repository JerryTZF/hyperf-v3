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

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 业务逻辑错误码和错误信息.
 * Class ErrorCode.
 */
#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("操作频繁, 请稍后再试")
     */
    public const ACT_BUSY = 50000;

    /**
     * @Message("验证码错误")
     */
    public const CAPTCHA_ERROR = 50001;

    /**
     * @Message("%s 库存不足")
     */
    public const STOCK_ERR = 50002;

    /**
     * @Message("太多人抢购，请稍后再试")
     */
    public const STOCK_BUSY = 50003;

    /**
     * @Message("% 消息投递失败")
     */
    public const QUEUE_PUSH_ERR = 50004;

    /**
     * @Message("MIME 类型错误")
     */
    public const MIME_ERROR = 50005;

    /**
     * @Message("用户 %s 未注册或者密码错误")
     */
    public const USER_NOT_FOUND = 50006;

    /**
     * @Message("用户 %s 已注册")
     */
    public const USER_HAD_REGISTERED = 50007;

    /**
     * @Message("jwt已失效 或者 该用户已被禁用")
     */
    public const DO_JWT_FAIL = 50008;

    /**
     * @Message("jwt 缺失")
     */
    public const JWT_EMPTY_ERR = 50009;

    /**
     * @Message("没有对应的角色")
     */
    public const ROLE_EMPTY = 50010;

    /**
     * @Message("部分或全部节点不存在")
     */
    public const AUTH_NOT_FOUND = 50011;

    /**
     * @Message("默认角色不允许变更")
     */
    public const DEFAULT_ROLE_PROTECT = 50012;

    /**
     * @Message("权限不足")
     */
    public const NO_AUTH = 50013;

    /**
     * @Message("超级管理员无需操作")
     */
    public const SUPER_ADMIN = 50014;

    /**
     * @Message("用户不存在")
     */
    public const USER_NONE = 50015;

    /**
     * @Message("密码一致未变更")
     */
    public const PWD_SAME = 50016;

    /**
     * @Message("短信还在有效期内, 请勿重复发送")
     */
    public const SMS_NOT_EXPIRED = 50017;

    /**
     * @Message("短信发送失败")
     */
    public const SMS_SEND_ERR = 50018;

    /**
     * @Message("该链接已存在短链, 请勿重复生成")
     */
    public const SHORT_CHAIN_EXIST = 50019;

    /**
     * @Message("短链已过期")
     */
    public const SHORT_CHAIN_EXPIRED = 50020;

    /**
     * @Message("未知的短链")
     */
    public const UNKNOWN_SHORT_CHAIN = 50021;

    /**
     * @Message("商品不存在")
     */
    public const GOOD_NOT_FOUND = 50022;

    /**
     * @Message("商品: %s 库存不足")
     */
    public const GOOD_STOCK_EMPTY = 50023;

    /**
     * @Message("购买失败, 库存不足")
     */
    public const STOCK_EMPTY = 50024;
}

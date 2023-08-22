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

#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    public const SERVER_ERROR = 500;

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
}

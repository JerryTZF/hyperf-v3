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

/**
 * 服务抽象类, 用于继承.
 * Class AbstractService.
 */
abstract class AbstractService
{
    /**
     * 根据错误码获取对应的错误信息和错误码.
     * @param int $errorCode 错误码
     * @param array $opt 信息填充
     * @param string $message 自定义错误信息
     * @return array 异常数组
     */
    public static function getErrorMap(int $errorCode, array $opt = [], string $message = ''): array
    {
        if ($message !== '') {
            return [$errorCode, $message];
        }
        if ($opt !== []) {
            return [$errorCode, ErrorCode::getMessage($errorCode, $opt)];
        }

        return [$errorCode, ErrorCode::getMessage($errorCode)];
    }
}

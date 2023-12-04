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

abstract class AbstractService
{
    public static function getErrorMap(int $errorCode, array $opt = []): array
    {
        return [$errorCode, ErrorCode::getMessage($errorCode, $opt)];
    }
}

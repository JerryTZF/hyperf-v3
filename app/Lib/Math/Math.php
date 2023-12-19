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

namespace App\Lib\Math;

class Math
{
    /**
     * 高精度两数做和(默认保留两位小数).
     */
    public static function add(mixed $v1, mixed $v2, int $scale = 2): string
    {
        [$v1, $v2] = [strval($v1), strval($v2)];
        return bcadd($v1, $v2, $scale);
    }

    /**
     * 高精度两数作差(默认保留两位小数).
     */
    public static function sub(mixed $v1, mixed $v2, int $scale = 2): string
    {
        [$v1, $v2] = [strval($v1), strval($v2)];
        return bcsub($v1, $v2, $scale);
    }

    /**
     * 高精度两数乘积(默认保留两位小数).
     */
    public static function mul(mixed $v1, mixed $v2, int $scale = 2): string
    {
        [$v1, $v2] = [strval($v1), strval($v2)];
        return bcmul($v1, $v2, $scale);
    }

    /**
     * 高精度两数(默认保留两位小数).
     */
    public static function div(mixed $v1, mixed $v2, int $scale = 2): string
    {
        [$v1, $v2] = [strval($v1), strval($v2)];
        return bcdiv($v1, $v2, $scale);
    }

    /**
     * 高精度比较两数大小(默认支持两位小数).
     * @return int -1: $v1<$v2; 0: $v1=$v2; 1: $v1>$v2
     */
    public static function compare(mixed $v1, mixed $v2, int $scale = 2): int
    {
        [$v1, $v2] = [strval($v1), strval($v2)];
        return bccomp($v1, $v2, $scale);
    }
}

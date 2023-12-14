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

namespace App\Lib\Algorithm;

/**
 * Murmur Hash 算法,用于短链生成.
 * Class Murmur.
 */
class Murmur
{
    /**
     * 字典.
     */
    public const DICT = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Murmur Hash.
     * @param string $key 带加密数据
     * @param int $seed 种子
     * @return int 返回值
     */
    public static function hash3Int(string $key, int $seed = 0): int
    {
        $key = array_values(unpack('C*', $key));
        $klen = count($key);
        $h1 = $seed < 0 ? -$seed : $seed;
        $remainder = $i = 0;
        for ($bytes = $klen - ($remainder = $klen & 3); $i < $bytes;) {
            $k1 = $key[$i]
                | ($key[++$i] << 8)
                | ($key[++$i] << 16)
                | ($key[++$i] << 24);
            ++$i;
            $k1 = ((($k1 & 0xFFFF) * 0xCC9E2D51) + (((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7FFFFFFF) >> 16) | 0x8000) * 0xCC9E2D51) & 0xFFFF) << 16)) & 0xFFFFFFFF;
            $k1 = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7FFFFFFF) >> 17) | 0x4000);
            $k1 = ((($k1 & 0xFFFF) * 0x1B873593) + (((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7FFFFFFF) >> 16) | 0x8000) * 0x1B873593) & 0xFFFF) << 16)) & 0xFFFFFFFF;
            $h1 ^= $k1;
            $h1 = $h1 << 13 | ($h1 >= 0 ? $h1 >> 19 : (($h1 & 0x7FFFFFFF) >> 19) | 0x1000);
            $h1b = ((($h1 & 0xFFFF) * 5) + (((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7FFFFFFF) >> 16) | 0x8000) * 5) & 0xFFFF) << 16)) & 0xFFFFFFFF;
            $h1 = ((($h1b & 0xFFFF) + 0x6B64) + (((($h1b >= 0 ? $h1b >> 16 : (($h1b & 0x7FFFFFFF) >> 16) | 0x8000) + 0xE654) & 0xFFFF) << 16));
        }
        $k1 = 0;
        switch ($remainder) {
            case 3:
                $k1 ^= $key[$i + 2] << 16;
                // no break
            case 2:
                $k1 ^= $key[$i + 1] << 8;
                // no break
            case 1:
                $k1 ^= $key[$i];
                $k1 = ((($k1 & 0xFFFF) * 0xCC9E2D51) + (((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7FFFFFFF) >> 16) | 0x8000) * 0xCC9E2D51) & 0xFFFF) << 16)) & 0xFFFFFFFF;
                $k1 = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7FFFFFFF) >> 17) | 0x4000);
                $k1 = ((($k1 & 0xFFFF) * 0x1B873593) + (((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7FFFFFFF) >> 16) | 0x8000) * 0x1B873593) & 0xFFFF) << 16)) & 0xFFFFFFFF;
                $h1 ^= $k1;
        }
        $h1 ^= $klen;
        $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7FFFFFFF) >> 16) | 0x8000);
        $h1 = ((($h1 & 0xFFFF) * 0x85EBCA6B) + (((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7FFFFFFF) >> 16) | 0x8000) * 0x85EBCA6B) & 0xFFFF) << 16)) & 0xFFFFFFFF;
        $h1 ^= ($h1 >= 0 ? $h1 >> 13 : (($h1 & 0x7FFFFFFF) >> 13) | 0x40000);
        $h1 = ((($h1 & 0xFFFF) * 0xC2B2AE35) + (((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7FFFFFFF) >> 16) | 0x8000) * 0xC2B2AE35) & 0xFFFF) << 16)) & 0xFFFFFFFF;
        $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7FFFFFFF) >> 16) | 0x8000);
        return $h1;
    }

    /**
     * Murmur Hash.
     * @param string $key 带加密数据
     * @param int $seed 种子
     * @return string 返回值
     */
    public static function hash3(string $key, int $seed = 0): string
    {
        return base_convert(sprintf("%u\n", self::hash3Int($key, $seed)), 10, 32);
    }

    /**
     * 10<->62转换.
     * @param int $num 数组
     * @return string 字符串
     */
    public static function from10To62(int $num): string
    {
        $result = '';
        do {
            $result = self::DICT[$num % 62] . $result;
            $num = intval($num / 62);
        } while ($num != 0);
        return $result;
    }

    /**
     * 62<->10转换.
     * @param string $str 待转换字符串
     * @return float|int 数字
     */
    public static function from62To10(string $str): float|int
    {
        $len = strlen($str);
        $dec = 0;
        for ($i = 0; $i < $len; ++$i) {
            // 找到对应字典的下标
            $pos = strpos(self::DICT, $str[$i]);
            $dec += $pos * pow(62, $len - $i - 1);
        }
        return $dec;
    }
}

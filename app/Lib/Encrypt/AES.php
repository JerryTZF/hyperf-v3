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
namespace App\Lib\Encrypt;

class AES
{
    /**
     * ecb方式加密. (不需要偏移量 && 默认 PKCS7Padding 填充方式).
     * @param array|string $data 待加密的数据
     * @param string $key 秘钥
     */
    public static function ecbEncryptHex(array|string $data, string $key, string $cipher = 'AES-128-ECB'): string
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data;
        $padding = OPENSSL_RAW_DATA;
        return bin2hex(openssl_encrypt($data, $cipher, $key, $padding));
    }

    /**
     * ecb方式解密. (不需要偏移量 && 默认 PKCS7Padding 填充方式).
     */
    public static function ecbDecryptHex(string $encryptData, string $key, string $cipher = 'AES-128-ECB'): string|array
    {
        $padding = OPENSSL_RAW_DATA;
        $decrypt = openssl_decrypt(hex2bin($encryptData), $cipher, $key, $padding);
        return json_decode($decrypt, true) ?? $decrypt;
    }

    /**
     * ecb方式加密. (不需要偏移量 && 默认 PKCS7Padding 填充方式).
     */
    public static function ecbEncryptBase64(array|string $data, string $key, string $cipher = 'AES-128-ECB'): string
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data;
        $padding = OPENSSL_RAW_DATA;
        return base64_encode(openssl_encrypt($data, $cipher, $key, $padding));
    }

    /**
     * ecb方式解密. (不需要偏移量 && 默认 PKCS7Padding 填充方式).
     */
    public static function ecbDecryptBase64(string $encryptData, string $key, string $cipher = 'AES-128-ECB'): string|array
    {
        $padding = OPENSSL_RAW_DATA;
        $decrypt = openssl_decrypt(base64_decode($encryptData), $cipher, $key, $padding);
        return json_decode($decrypt, true) ?? $decrypt;
    }

    /**
     * CBC方式加密(PKCS7Padding 填充方式).
     */
    public static function cbcEncryptHex(array|string $data, string $key, string $iv, string $cipher = 'AES-128-CBC'): string
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data;
        $padding = OPENSSL_RAW_DATA;
        $iv = md5($iv, true); // 注意别的语言是否是这种方式固定16位长度!!!
        return bin2hex(openssl_encrypt($data, $cipher, $key, $padding, $iv));
    }

    /**
     * CBC方式解密(PKCS7Padding 填充方式).
     */
    public static function cbcDecryptHex(string $encryptData, string $key, string $iv, string $cipher = 'AES-128-CBC'): string|array
    {
        $padding = OPENSSL_RAW_DATA;
        $iv = md5($iv, true); // 注意别的语言是否是这种方式固定16位长度!!!
        $decrypt = openssl_decrypt(hex2bin($encryptData), $cipher, $key, $padding, $iv);
        return json_decode($decrypt, true) ?? $decrypt;
    }

    /**
     * CBC方式加密(PKCS7Padding 填充方式).
     */
    public static function cbcEncryptBase64(array|string $data, string $key, string $iv, string $cipher = 'AES-128-CBC'): string
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data;
        $padding = OPENSSL_RAW_DATA;
        $iv = md5($iv, true); // 注意别的语言是否是这种方式固定16位长度!!!
        return base64_encode(openssl_encrypt($data, $cipher, $key, $padding, $iv));
    }

    /**
     * CBC方式解密(PKCS7Padding 填充方式).
     */
    public static function cbcDecryptBase64(string $encryptData, string $key, string $iv, string $cipher = 'AES-128-CBC'): string|array
    {
        $padding = OPENSSL_RAW_DATA;
        $iv = md5($iv, true); // 注意别的语言是否是这种方式固定16位长度!!!
        $decrypt = openssl_decrypt(base64_decode($encryptData), $cipher, $key, $padding, $iv);
        return json_decode($decrypt, true) ?? $decrypt;
    }
}

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

use App\Lib\Encrypt\AesWithPHPSeclib;

class EncryptService extends AbstractService
{
    /**
     * AES加密.
     * @param string $key 秘钥
     * @param array|string $data 待加密数据
     * @param string $cipherType 密码类型
     * @param int $cipherLength 密码类型长度
     * @param string $type 输出类型
     * @param array $option 辅助参数
     * @return string 加密后数据
     */
    public function aesEncrypt(
        string $key,
        string $cipherType = 'ecb',
        int $cipherLength = 256,
        string $type = 'base64',
        array $option = [],
        string|array $data = '',
    ): string {
        $aesInstance = new AesWithPHPSeclib($cipherType, $cipherLength, $key, $option);
        return $type === 'base64' ? $aesInstance->encryptBase64($data) : $aesInstance->encryptHex($data);
    }
}

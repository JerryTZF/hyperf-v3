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

use phpseclib3\Crypt\Common\SymmetricKey;

class AesWithPHPSeclib
{
    /**
     * aes实例.
     */
    private \phpseclib3\Crypt\AES $aesInstance;

    /**
     * 构造函数.
     */
    public function __construct(string $mode, int $keyLength, string $key, array $options = [])
    {
        $mode = strtolower($mode);
        $aesInstance = new \phpseclib3\Crypt\AES($mode);
        $aesInstance->setPreferredEngine(SymmetricKey::ENGINE_OPENSSL);
        $aesInstance->setKeyLength($keyLength);
        $aesInstance->enablePadding();

        // ecb 和 gcm 不用添加 $iv
        if (! in_array(SymmetricKey::MODE_MAP[$mode], [SymmetricKey::MODE_GCM, SymmetricKey::MODE_ECB])) {
            // 注意别的语言是否是这种方式固定16位长度!!!
            $iv = md5($options['iv'] ?? '', true);
            $aesInstance->setIV($iv);
        }

        // 只有gcm 才有tag、nonce、aad
        if (SymmetricKey::MODE_MAP[$mode] === SymmetricKey::MODE_GCM) {
            $aesInstance->setTag($options['tag'] ?? '');
            $aesInstance->setNonce($options['nonce'] ?? '');
            $aesInstance->setAAD($options['aad'] ?? '');
        }

        $aesInstance->setKey($key);
        $this->aesInstance = $aesInstance;
    }

    /**
     * 加密输出Hex字符串.
     */
    public function encryptHex(array|string $data): string
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data;
        return bin2hex($this->aesInstance->encrypt($data));
    }

    /**
     * 解密Hex字符串.
     */
    public function decryptHex(string $decryptText): string|array
    {
        $data = $this->aesInstance->decrypt(hex2bin($decryptText));
        return json_decode($data, true) ?? $data;
    }

    /**
     * 加密输出base64字符串.
     */
    public function encryptBase64(array|string $data): string
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data;
        return base64_encode($this->aesInstance->encrypt($data));
    }

    /**
     * 解密Base64字符串.
     */
    public function decryptBase64(string $decryptText): string|array
    {
        $data = $this->aesInstance->decrypt(base64_decode($decryptText));
        return json_decode($data, true) ?? $data;
    }
}

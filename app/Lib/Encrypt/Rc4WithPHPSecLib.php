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

use phpseclib3\Crypt\RC4;

class Rc4WithPHPSecLib
{
    /**
     * RC4实例对象.
     */
    private RC4 $RC4;

    /**
     * 秘钥.
     */
    private string $key;

    /**
     * 构造函数.
     */
    public function __construct(string $key = 'abc')
    {
        $this->key = $key;
        $this->RC4 = new RC4();

        $this->RC4->setKey($this->key);
        // TODO $RC4 还有一些设置IV等, 这里不作展开 :)
    }

    /**
     * 加密.
     */
    public function encrypt(string|array $message): string
    {
        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $message;
        return base64_encode($this->RC4->encrypt($message));
    }

    /**
     * 解密.
     */
    public function decrypt(string $encryptData): string|array
    {
        $decryptData = $this->RC4->decrypt(base64_decode($encryptData));
        return json_decode($decryptData, true) ?? $decryptData;
    }

    /**
     * 原生加密.
     */
    public function encryptNative(string|array $message): string
    {
        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $message;
        return base64_encode($this->native($this->key, $message));
    }

    /**
     * 原生解密.
     */
    public function decryptNative(string $encryptData): string|array
    {
        $decryptData = $this->native($this->key, base64_decode($encryptData));
        return json_decode($decryptData, true) ?? $decryptData;
    }

    /**
     * 原生算法.
     */
    private function native(string $key, string $data): string
    {
        $cipher = '';
        $keyMap[] = '';
        $box[] = '';
        $pwdLength = strlen($key);
        $data_length = strlen($data);
        for ($i = 0; $i < 256; ++$i) {
            $keyMap[$i] = ord($key[$i % $pwdLength]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; ++$i) {
            $j = ($j + $box[$i] + $keyMap[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $data_length; ++$i) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $k = $box[($box[$a] + $box[$j]) % 256];
            $cipher .= chr(ord($data[$i]) ^ $k);
        }
        return $cipher;
    }
}

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

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey;

/**
 * Rsa加解密.
 * Class RsaWithPHPSeclib.
 * 在线工具测试: https://try8.cn/tool/cipher/rsa.
 */
class RsaWithPHPSeclib
{
    /**
     * RSA私钥实例.
     * @var PrivateKey|RSA rsa实例
     */
    private PrivateKey|RSA $privateKey;

    /**
     * 秘钥保存路径.
     * @var string 秘钥路径
     */
    private string $path;

    /**
     * 秘钥保存格式(PKCS8|PKCS1).
     * @see https://phpseclib.com/docs/publickeys#saving-keys
     * @var string 保存格式
     */
    private string $keyFormat;

    /**
     * 可用的hash算法.
     * @var array|string[]
     */
    private array $availableHash = ['md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224'];

    /**
     * 可用的加密填充方式.
     * @var array|int[]
     */
    private array $availableEncryptPadding = [RSA::ENCRYPTION_OAEP, RSA::ENCRYPTION_PKCS1, RSA::ENCRYPTION_NONE];

    /**
     * 私钥加签可用的填充方式.
     * @var array|int[]
     */
    private array $availableSignaturePadding = [RSA::SIGNATURE_PKCS1, RSA::SIGNATURE_PSS, RSA::SIGNATURE_RELAXED_PKCS1];

    /**
     * 加解密填充方式(RSA::ENCRYPTION_OAEP, RSA::ENCRYPTION_PKCS1, RSA::ENCRYPTION_NONE).
     * @see https://phpseclib.com/docs/rsa#encryption--decryption
     * @var int 填充
     */
    private int $encryptPadding;

    /**
     * 私钥加签填充方式.
     * @see https://phpseclib.com/docs/rsa#creating--verifying-signatures
     * @var int 填充
     */
    private int $signaturePadding;

    /**
     * 加解(解密)|加签(验签) 单向HASH算法('md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224').
     * @var string 'md5|md2....'
     */
    private string $hash;

    /**
     * 加解(解密)|加签(验签) 单向mgfHASH算法('md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224').
     * @var string 'md5|md2....'
     */
    private string $mgfHash;

    /**
     * 构造函数.
     * @param null|string $password 证书密码
     * @param int $length 秘钥长度
     * @param string $keyFormat 秘钥格式 PKCS8 || PKCS1
     * @param int $encryptPadding 填充模式 (RSA::ENCRYPTION_OAEP, RSA::ENCRYPTION_PKCS1, RSA::ENCRYPTION_NONE)
     * @param string $encryptHash HASH算法
     * @param string $encryptMgfHash MGF HASH算法
     * @param int $signaturePadding 签名填充模式 (RSA::SIGNATURE_PKCS1, RSA::SIGNATURE_PSS)
     */
    public function __construct(
        string $password = null,
        int $length = 2048,
        string $keyFormat = 'PKCS8',
        int $encryptPadding = RSA::ENCRYPTION_PKCS1,
        string $encryptHash = 'sha256',
        string $encryptMgfHash = 'sha256',
        int $signaturePadding = RSA::SIGNATURE_PKCS1,
    ) {
        $privateKey = RSA::createKey($length);
        if ($password !== null) {
            $privateKey = $privateKey->withPassword($password);
        }

        $this->privateKey = $privateKey;
        $this->path = BASE_PATH . '/runtime/openssl/';
        $this->keyFormat = $keyFormat;
        $this->encryptPadding = $encryptPadding;
        $this->hash = $encryptHash;
        $this->mgfHash = $encryptMgfHash;
        $this->signaturePadding = $signaturePadding;

        if (! is_dir($this->path)) {
            mkdir(iconv('GBK', 'UTF-8', $this->path), 0755);
        }

        // 初步创建并保存秘钥
        $this->createKeys();
    }

    /**
     * 获取私钥字符串.
     * @return string 私钥证书字符串
     */
    public function getPrivateKeyString(): string
    {
        return $this->privateKey->toString($this->keyFormat);
    }

    /**
     * 获取公钥字符串.
     * @return string 公钥证书字符串
     */
    public function getPublicKeyString(): string
    {
        return $this->privateKey->getPublicKey()->toString($this->keyFormat);
    }

    /**
     * 公钥加密.
     * @param array|string $message 待加密数据
     * @return string 加密后数据
     */
    public function publicKeyEncrypt(array|string $message): string
    {
        $privateKey = $this->buildPrivateKey('encrypt');
        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $message;
        return base64_encode($privateKey->getPublicKey()->encrypt($message));
    }

    /**
     * 私钥解密.
     * @param string $encryptText 待解密数据
     * @return array|string 解密后数据
     */
    public function privateKeyDecrypt(string $encryptText): array|string
    {
        $privateKey = $this->buildPrivateKey('encrypt');
        $decryptData = $privateKey->decrypt(base64_decode($encryptText));
        return json_decode($decryptData, true) ?? $decryptData;
    }

    /**
     * 私钥加签.
     * @param array|string $message 待加密数据
     * @return string 加密后数据
     */
    public function privateKeySign(array|string $message): string
    {
        $privateKey = $this->buildPrivateKey('signature');
        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $message;
        return base64_encode($privateKey->sign($message));
    }

    /**
     * 公钥验签.
     * @param array|string $message 待加签数据
     * @param string $signature 签名
     * @return bool 是否合法
     */
    public function publicKeyVerifySign(array|string $message, string $signature): bool
    {
        $privateKey = $this->buildPrivateKey('signature');
        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $message;
        return $privateKey->getPublicKey()->verify($message, base64_decode($signature));
    }

    /**
     * 创建公私钥对(不存在才会创建, 不会覆盖).
     */
    private function createKeys(): void
    {
        [$publicKey, $privateKey] = [
            $this->path . 'public.pem',
            $this->path . 'private.pem',
        ];
        if (! file_exists($publicKey) || ! file_exists($privateKey)) {
            file_put_contents($privateKey, $this->privateKey->toString($this->keyFormat));
            file_put_contents($publicKey, $this->privateKey->getPublicKey()->toString($this->keyFormat));
        }
    }

    /**
     * 构建不同场景下的合法私钥.
     * @param string $mode 模式
     * @return PrivateKey|RSA rsa实例
     */
    private function buildPrivateKey(string $mode = 'encrypt'): PrivateKey|RSA
    {
        /** @var PrivateKey|RSA $privateKey */
        $privateKey = RSA::loadPrivateKey(file_get_contents($this->path . 'private.pem'));
        if ($mode === 'encrypt') {
            // 加签填充方式
            $privateKey = in_array($this->signaturePadding, $this->availableSignaturePadding) ?
                $privateKey->withPadding($this->signaturePadding) : $privateKey->withPadding(RSA::SIGNATURE_PKCS1);
        } else {
            // 加密填充方式
            $privateKey = in_array($this->encryptPadding, $this->availableEncryptPadding) ?
                $privateKey->withPadding($this->encryptPadding) : $privateKey->withPadding(RSA::ENCRYPTION_PKCS1);
        }

        // HASH方式
        $privateKey = in_array($this->hash, $this->availableHash) ?
            $privateKey->withHash($this->hash) : $privateKey->withHash('sha256');
        // MGF HASH方式(只有padding为RSA::ENCRYPTION_OAEP可用)
        return in_array($this->mgfHash, $this->availableHash) ?
            $privateKey->withMGFHash($this->mgfHash) : $privateKey->withHash('sha256');
    }
}

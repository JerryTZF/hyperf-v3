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

// 在线工具测试:
// https://try8.cn/tool/cipher/rsa
class RsaWithPHPSeclib
{
    /**
     * RSA私钥实例.
     */
    private RSA|PrivateKey $privateKey;

    /**
     * 秘钥保存路径.
     */
    private string $path;

    /**
     * 秘钥保存格式(PKCS8|PKCS1).
     * @see https://phpseclib.com/docs/publickeys#saving-keys
     */
    private string $keyFormat;

    /**
     * 可用的hash算法.
     */
    private array $availableHash = ['md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224'];

    /**
     * 可用的加密填充方式.
     */
    private array $availableEncryptPadding = [RSA::ENCRYPTION_OAEP, RSA::ENCRYPTION_PKCS1, RSA::ENCRYPTION_NONE];

    /**
     * 私钥加签可用的填充方式.
     */
    private array $availableSignaturePadding = [RSA::SIGNATURE_PKCS1, RSA::SIGNATURE_PSS, RSA::SIGNATURE_RELAXED_PKCS1];

    /**
     * 加解密填充方式(RSA::ENCRYPTION_OAEP, RSA::ENCRYPTION_PKCS1, RSA::ENCRYPTION_NONE).
     * @see https://phpseclib.com/docs/rsa#encryption--decryption
     */
    private int $encryptPadding;

    /**
     * 私钥加签填充方式.
     * @see https://phpseclib.com/docs/rsa#creating--verifying-signatures
     */
    private int $signaturePadding;

    /**
     * 加解(解密)|加签(验签) 单向HASH算法('md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224').
     */
    private string $hash;

    /**
     * 加解(解密)|加签(验签) 单向mgfHASH算法('md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224').
     */
    private string $mgfHash;

    /**
     * 构造函数.
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
     * 公钥加密.
     */
    public function publicKeyEncrypt(string|array $message): string
    {
        /** @var PrivateKey $privateKey */
        $privateKey = RSA::loadPrivateKey(file_get_contents($this->path . 'private.pem'));
        // 加密填充方式
        $privateKey = in_array($this->encryptPadding, $this->availableEncryptPadding) ?
            $privateKey->withPadding($this->encryptPadding) : $privateKey->withPadding(RSA::ENCRYPTION_PKCS1);
        // HASH方式
        $privateKey = in_array($this->hash, $this->availableHash) ?
            $privateKey->withHash($this->hash) : $privateKey->withHash('sha256');
        // MGF HASH方式(只有padding为RSA::ENCRYPTION_OAEP可用)
        $privateKey = in_array($this->mgfHash, $this->availableHash) ?
            $privateKey->withMGFHash($this->mgfHash) : $privateKey->withHash('sha256');

        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $message;

        return base64_encode($privateKey->getPublicKey()->encrypt($message));
    }

    /**
     * 私钥解密.
     */
    public function privateKeyDecrypt(string $encryptText): array|string
    {
        /** @var PrivateKey|RSA $privateKey */
        $privateKey = RSA::loadPrivateKey(file_get_contents($this->path . 'private.pem'));
        // 加密填充方式
        $privateKey = in_array($this->encryptPadding, $this->availableEncryptPadding) ?
            $privateKey->withPadding($this->encryptPadding) : $privateKey->withPadding(RSA::ENCRYPTION_PKCS1);
        // HASH方式
        $privateKey = in_array($this->hash, $this->availableHash) ?
            $privateKey->withHash($this->hash) : $privateKey->withHash('sha256');
        // MGF HASH方式(只有padding为RSA::ENCRYPTION_OAEP可用)
        $privateKey = in_array($this->mgfHash, $this->availableHash) ?
            $privateKey->withMGFHash($this->mgfHash) : $privateKey->withHash('sha256');

        $decryptData = $privateKey->decrypt(base64_decode($encryptText));
        return json_decode($decryptData, true) ?? $decryptData;
    }

    /**
     * 私钥加签.
     */
    public function privateKeySign(string|array $message): string
    {
        /** @var PrivateKey|RSA $privateKey */
        $privateKey = RSA::loadPrivateKey(file_get_contents($this->path . 'private.pem'));
        // 加签填充方式
        $privateKey = in_array($this->signaturePadding, $this->availableSignaturePadding) ?
            $privateKey->withPadding($this->signaturePadding) : $privateKey->withPadding(RSA::SIGNATURE_PKCS1);
        // HASH方式
        $privateKey = in_array($this->hash, $this->availableHash) ?
            $privateKey->withHash($this->hash) : $privateKey->withHash('sha256');
        $privateKey = in_array($this->mgfHash, $this->availableHash) ?
            $privateKey->withMGFHash($this->mgfHash) : $privateKey->withHash('sha256');

        $message = is_array($message) ? json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $message;

        return base64_encode($privateKey->sign($message));
    }

    /**
     * 公钥验签.
     */
    public function publicKeyVerifySign(string|array $message, string $signature): bool
    {
        /** @var PrivateKey|RSA $privateKey */
        $privateKey = RSA::loadPrivateKey(file_get_contents($this->path . 'private.pem'));
        // 加签填充方式
        $privateKey = in_array($this->signaturePadding, $this->availableSignaturePadding) ?
            $privateKey->withPadding($this->signaturePadding) : $privateKey->withPadding(RSA::SIGNATURE_PKCS1);
        // HASH方式
        $privateKey = in_array($this->hash, $this->availableHash) ?
            $privateKey->withHash($this->hash) : $privateKey->withHash('sha256');
        $privateKey = in_array($this->mgfHash, $this->availableHash) ?
            $privateKey->withMGFHash($this->mgfHash) : $privateKey->withHash('sha256');

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
}

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

use App\Lib\Encrypt\RsaWithPHPSeclib;
use App\Lib\File\Zip;
use Carbon\Carbon;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PublicKey;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

class EncryptService extends AbstractService
{
    /**
     * 获取RSA公私钥秘钥对.
     * @param string $keyFormat 秘钥格式
     * @param int $keyLen 密钥长度
     * @param null|string $certificatePassword 证书密码
     * @param bool $isDownload 是否下载公私钥
     * @return array|MessageInterface|ResponseInterface 响应
     */
    public function createRSA(
        string $keyFormat,
        int $keyLen = 2048,
        string $certificatePassword = null,
        bool $isDownload = false,
    ): array|MessageInterface|ResponseInterface {
        $rsaInstance = new RsaWithPHPSeclib($certificatePassword, $keyLen, $keyFormat);
        if ($isDownload) {
            $certificateList = [
                BASE_PATH . '/runtime/openssl/private.pem',
                BASE_PATH . '/runtime/openssl/public.pem',
            ];
            $zipName = 'rsa-' . Carbon::now()->timestamp . '.zip';
            $zipPath = BASE_PATH . '/runtime/openssl/' . $zipName;
            Zip::compress($zipPath, $certificateList);

            $response = new Response();
            return $response->withHeader('content-description', 'File Transfer')
                ->withHeader('content-type', 'application/zip')
                ->withHeader('content-disposition', 'attachment; filename="' . $zipName . '"')
                ->withHeader('content-transfer-encoding', 'binary')
                ->withBody(new SwooleStream((string) file_get_contents($zipPath)));
        }
        return [
            'public_key' => $rsaInstance->getPublicKeyString(),
            'private_key' => $rsaInstance->getPrivateKeyString(),
        ];
    }

    /**
     * 公钥加密.
     * @param string $key 公钥
     * @param string $encryptPadding 加解密填充方式
     * @param string $hash 单向哈希
     * @param array|string $data 待加密数据
     * @param string $mgfHash 当加密填充模式为OAEP或签名填充模式为PSS使用
     * @return string 加密结果
     */
    public function publicKeyEncrypt(
        string $key,
        string $encryptPadding,
        string $hash,
        string|array $data,
        string $mgfHash = 'sha256',
    ): string {
        /** @var PublicKey|RSA $publicKey */
        $publicKey = RSA::loadPublicKey($key);
        // 是否需要mgfHash
        if ($encryptPadding === 'ENCRYPTION_OAEP') {
            $publicKey = $publicKey->withHash($hash)->withMGFHash($mgfHash);
        } else {
            $publicKey = $publicKey->withHash($hash);
        }
        $encryptPadding = match ($encryptPadding) {
            'ENCRYPTION_OAEP' => RSA::ENCRYPTION_OAEP,
            'ENCRYPTION_PKCS1' => RSA::ENCRYPTION_PKCS1,
            default => RSA::ENCRYPTION_NONE,
        };
        $publicKey = $publicKey->withPadding($encryptPadding);
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $data;
        return base64_encode($publicKey->encrypt($data));
    }

    /**
     * 公钥验签.
     * @param string $key 公钥
     * @param string $signPadding 签名填充方式
     * @param string $hash 单向哈希
     * @param array|string $data 加签的原数据
     * @param string $sign 签名
     * @param string $mgfHash 当加密填充模式为OAEP或签名填充模式为PSS使用
     * @return bool 验签结果
     */
    public function publicKeyVerifySign(
        string $key,
        string $signPadding,
        string $hash,
        string|array $data,
        string $sign,
        string $mgfHash = 'sha256',
    ): bool {
        /** @var PublicKey|RSA $publicKey */
        $publicKey = RSA::loadPublicKey($key);
        // 是否需要mgfHash
        if ($signPadding === 'SIGNATURE_PSS') {
            $publicKey = $publicKey->withHash($hash)->withMGFHash($mgfHash);
        } else {
            $publicKey = $publicKey->withHash($hash);
        }
        $signPadding = match ($signPadding) {
            'SIGNATURE_PKCS1' => RSA::SIGNATURE_PKCS1,
            'SIGNATURE_PSS' => RSA::SIGNATURE_PSS,
        };
        $publicKey = $publicKey->withPadding($signPadding);
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $data;
        return $publicKey->verify($data, base64_decode($sign));
    }

    /**
     * 私钥解密.
     * @param string $key 私钥
     * @param string $encryptPadding 加解密填充方式
     * @param string $hash 单向哈希
     * @param array|string $data 待加密数据
     * @param string $mgfHash 当加密填充模式为OAEP或签名填充模式为PSS使用
     * @param string $password 证书密码
     * @return mixed 解密结果
     */
    public function privateKeyDecrypt(
        string $key,
        string $encryptPadding,
        string $hash,
        string|array $data,
        string $mgfHash = 'sha256',
        string $password = '',
    ): mixed {
        /** @var PrivateKey|RSA $privateKey */
        $privateKey = RSA::loadPrivateKey($key, $password);
        // 是否需要mgfHash
        if ($encryptPadding === 'ENCRYPTION_OAEP') {
            $privateKey = $privateKey->withHash($hash)->withMGFHash($mgfHash);
        } else {
            $privateKey = $privateKey->withHash($hash);
        }
        $encryptPadding = match ($encryptPadding) {
            'ENCRYPTION_OAEP' => RSA::ENCRYPTION_OAEP,
            'ENCRYPTION_PKCS1' => RSA::ENCRYPTION_PKCS1,
            default => RSA::ENCRYPTION_NONE,
        };
        $privateKey = $privateKey->withPadding($encryptPadding);
        $decrypt = $privateKey->decrypt(base64_decode($data));
        return json_decode($decrypt, true) ?? $decrypt;
    }

    /**
     * 私钥签名.
     * @param string $key 私钥
     * @param string $signPadding 签名填充方式
     * @param string $hash 单向哈希
     * @param array|string $data 待签名数据
     * @param string $mgfHash 当加密填充模式为OAEP或签名填充模式为PSS使用
     * @param string $password 证书密码
     * @return string 签名
     */
    public function privateKeySign(
        string $key,
        string $signPadding,
        string $hash,
        string|array $data,
        string $mgfHash = 'sha256',
        string $password = '',
    ): string {
        /** @var PrivateKey|RSA $privateKey */
        $privateKey = RSA::loadPrivateKey($key, $password);
        //        RSA::SIGNATURE_PKCS1, RSA::SIGNATURE_PSS
        // 是否需要mgfHash
        if ($signPadding === 'SIGNATURE_PSS') {
            $privateKey = $privateKey->withHash($hash)->withMGFHash($mgfHash);
        } else {
            $privateKey = $privateKey->withHash($hash);
        }
        $signPadding = match ($signPadding) {
            'SIGNATURE_PKCS1' => RSA::SIGNATURE_PKCS1,
            'SIGNATURE_PSS' => RSA::SIGNATURE_PSS,
        };
        $privateKey = $privateKey->withPadding($signPadding);
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $data;
        return base64_encode($privateKey->sign($data));
    }
}

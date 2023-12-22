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

namespace App\Controller;

use App\Lib\Encrypt\AesWithPHPSeclib;
use App\Lib\Encrypt\Rc4WithPHPSecLib;
use App\Request\EncryptRequest;
use App\Service\EncryptService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 加解密相关.
 * Class EncryptController.
 */
#[Controller(prefix: 'encryption')]
class EncryptController extends AbstractController
{
    #[Inject]
    protected EncryptService $service;

    /**
     * RC4加密.
     * @param EncryptRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'rc4/encrypt')]
    #[Scene(scene: 'rc4')]
    public function rc4Encrypt(EncryptRequest $request): array
    {
        $key = $request->input('key');
        $data = $request->input('data');
        $rc4 = new Rc4WithPHPSecLib($key);
        $result = $rc4->encrypt($data);
        return $this->result->setData(['encrypt_result' => $result])->getResult();
    }

    /**
     * RC4解密.
     * @param EncryptRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'rc4/decrypt')]
    #[Scene(scene: 'rc4')]
    public function rc4Decrypt(EncryptRequest $request): array
    {
        $key = $request->input('key');
        $data = $request->input('data');
        $rc4 = new Rc4WithPHPSecLib($key);
        $result = $rc4->decrypt($data);
        return $this->result->setData(['decrypt_result' => $result])->getResult();
    }

    /**
     * AES加密数据.
     * @param EncryptRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'aes/encrypt')]
    #[Scene(scene: 'aes')]
    public function aesEncrypt(EncryptRequest $request): array
    {
        [$key, $cipherType, $cipherLength, $type, $option, $data] = [
            $request->input('key'), // 秘钥
            $request->input('cipher_type', 'ecb'), // 密码类型
            intval($request->input('cipher_length', 256)), // 密码学长度
            $request->input('output_type', 'base64'), // 加密后转换类型(支持hex和base64)
            $request->input('option'), // 不同的密码类型所需要的参数也不一样
            $request->input('data'),
        ];

        $seclib = new AesWithPHPSeclib($cipherType, $cipherLength, $key, $option);
        $result = $type === 'base64' ? $seclib->encryptBase64($data) : $seclib->encryptHex($data);
        return $this->result->setData(['encrypt_result' => $result])->getResult();
    }

    /**
     * AES解密.
     * @param EncryptRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'aes/decrypt')]
    #[Scene(scene: 'aes')]
    public function aesDecrypt(EncryptRequest $request): array
    {
        [$key, $cipherType, $cipherLength, $type, $option, $encryptText] = [
            $request->input('key'), // 秘钥
            $request->input('cipher_type', 'ecb'), // 密码类型
            intval($request->input('cipher_length', 256)), // 密码学长度
            $request->input('output_type', 'base64'), // 加密后转换类型(支持hex和base64)
            $request->input('option'), // 不同的密码类型所需要的参数也不一样
            $request->input('data'),
        ];

        $seclib = new AesWithPHPSeclib($cipherType, $cipherLength, $key, $option);
        $result = $type === 'base64' ? $seclib->decryptBase64($encryptText) : $seclib->decryptHex($encryptText);
        return $this->result->setData(['decrypt_result' => $result])->getResult();
    }

    /**
     * 创建RSA公私钥秘钥对.
     * @param EncryptRequest $request 请求验证器
     * @return array|MessageInterface|ResponseInterface 响应
     */
    #[PostMapping(path: 'rsa/create')]
    #[Scene(scene: 'rsa_create')]
    public function createRsa(EncryptRequest $request): array|MessageInterface|ResponseInterface
    {
        $keyFormat = $request->input('key_format'); // PKCS1 || PKCS8
        $keyLength = $request->input('key_length'); // 1024 || 2048 || 3072 || 4096
        $isDownload = $request->input('is_download', false);
        $certificatePassword = $request->input('certificate_password');

        $result = $this->service->createRSA($keyFormat, $keyLength, $certificatePassword, $isDownload);
        if (is_array($result)) {
            return $this->result->setData($result)->getResult();
        }

        return $result;
    }

    /**
     * 公钥加密.
     * @param EncryptRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'rsa/public_key/encrypt')]
    #[Scene(scene: 'encrypt_decrypt')]
    public function publicKeyEncrypt(EncryptRequest $request): array
    {
        [$key, $padding, $hash, $mgfHash, $data] = [
            $request->input('key'), // 公钥
            $request->input('padding'), // 加密填充方式
            $request->input('hash'), // 哈希算法
            $request->input('mgf_hash', ''), // mgf哈希算法 当加密填充模式为OAEP或签名填充模式为PSS使用
            $request->input('data'), // 待加密数据
        ];

        $encryptResult = $this->service->publicKeyEncrypt($key, $padding, $hash, $data, $mgfHash);
        return $this->result->setData(['encrypt_result' => $encryptResult])->getResult();
    }

    /**
     * 私钥解密.
     * @param EncryptRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'rsa/private_key/decrypt')]
    #[Scene(scene: 'encrypt_decrypt')]
    public function privateKeyDecrypt(EncryptRequest $request): array
    {
        [$key, $padding, $hash, $mgfHash, $data, $password] = [
            $request->input('key'), // 私钥
            $request->input('padding'), // 加密填充方式
            $request->input('hash'), // 哈希算法
            $request->input('mgf_hash', ''), // mgf哈希算法 当加密填充模式为OAEP或签名填充模式为PSS使用
            $request->input('data'), // 待解密数据
            $request->input('password', ''), // 证书密码
        ];

        $decryptResult = $this->service->privateKeyDecrypt($key, $padding, $hash, $data, $mgfHash, $password);
        return $this->result->setData(['decrypt_result' => $decryptResult])->getResult();
    }

    /**
     * 私钥加签.
     * @param EncryptRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'rsa/private_key/sign')]
    #[Scene(scene: 'sign')]
    public function privateKeySign(EncryptRequest $request): array
    {
        [$key, $padding, $hash, $mgfHash, $data, $password] = [
            $request->input('key'), // 私钥
            $request->input('padding'), // 加密填充方式
            $request->input('hash'), // 哈希算法
            $request->input('mgf_hash', ''), // mgf哈希算法 当加密填充模式为OAEP或签名填充模式为PSS使用
            $request->input('data'), // 待解密数据
            $request->input('password', ''), // 证书密码
        ];

        $sign = $this->service->privateKeySign($key, $padding, $hash, $data, $mgfHash, $password);
        return $this->result->setData(['sign' => $sign])->getResult();
    }

    /**
     * 公钥验签.
     * @param EncryptRequest $request 请求验证器
     * @return array ['code' => '200', 'msg' => 'ok', 'status' => true, 'data' => []]
     */
    #[PostMapping(path: 'rsa/public_key/verify')]
    #[Scene(scene: 'sign')]
    public function publicKeyVerifySign(EncryptRequest $request): array
    {
        [$key, $padding, $hash, $mgfHash, $data, $sign] = [
            $request->input('key'), // 公钥
            $request->input('padding'), // 签名填充方式
            $request->input('hash'), // 哈希算法
            $request->input('mgf_hash', ''), // mgf哈希算法 当加密填充模式为OAEP或签名填充模式为PSS使用
            $request->input('data'), // 加签源数据
            $request->input('sign', ''), // 签名
        ];

        $verifyResult = $this->service->publicKeyVerifySign($key, $padding, $hash, $data, $sign, $mgfHash);
        return $this->result->setData(['verify_sign_result' => $verifyResult])->getResult();
    }
}

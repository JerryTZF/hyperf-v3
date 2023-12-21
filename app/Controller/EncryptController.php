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

    #[PostMapping(path: 'rsa/create')]
    #[Scene(scene: 'rsa_create')]
    public function createRsa(EncryptRequest $request): MessageInterface|array|ResponseInterface
    {
        $keyFormat = $request->input('key_format');
        $keyLength = $request->input('key_length');
        $isDownload = $request->input('is_download', false);
        $certificatePassword = $request->input('certificate_password');

        $result = $this->service->createRSA($keyFormat, $keyLength, $certificatePassword, $isDownload);
        if (is_array($result)) {
            return $this->result->setData($result)->getResult();
        }

        return $result;
    }
}

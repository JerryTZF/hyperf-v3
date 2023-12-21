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
    ): MessageInterface|array|ResponseInterface {
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
}

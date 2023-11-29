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

namespace App\Controller\Test;

use App\Constants\ConstCode;
use App\Controller\AbstractController;
use App\Lib\File\FileSystem;
use App\Lib\Image\Qrcode;
use App\Request\ImageRequest;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Zxing\QrReader;

#[Controller(prefix: 'image')]
class ImageController extends AbstractController
{
    // 制作二维码且上传到OSS返回二维码地址
    #[Scene(scene: 'qrcode')]
    #[PostMapping(path: 'qrcode/upload')]
    public function uploadQrcodeToOss(ImageRequest $request): array
    {
        $config = $this->buildQrcodeConfig($request);
        $qrCodeString = (new Qrcode($config))->getStream($config['content']);
        $config['logo_path'] !== '' && unlink($config['logo_path']); // 移除logo临时文件

        $fileFactory = new FileSystem();
        $path = '/img/' . uniqid() . '_qrcode.png';
        $fileFactory->write($path, $qrCodeString);

        return $this->result->setData(['url' => ConstCode::OSS_DOMAIN . $path])->getResult();
    }

    // 识别二维码
    #[Scene(scene: 'decode')]
    #[PostMapping(path: 'qrcode/decode')]
    public function decodeQrcode(ImageRequest $request): array
    {
        [$uploadQrcode, $qrcodeUrl] = [
            $request->file('qrcode'),
            $request->input('target_url'),
        ];

        $qrcodeString = $uploadQrcode !== null ? $uploadQrcode->getStream()->getContents() : file_get_contents($qrcodeUrl);
        $qrReader = new QrReader($qrcodeString, QrReader::SOURCE_TYPE_BLOB);
        $text = $qrReader->text() ?: '';

        return $this->result->setData(['text' => $text])->getResult();
    }

    // 下载二维码
    #[Scene(scene: 'qrcode')]
    #[PostMapping(path: 'qrcode/download')]
    public function downloadQrcode(ImageRequest $request): MessageInterface|ResponseInterface
    {
        $config = $this->buildQrcodeConfig($request);
        $qrCodeString = (new Qrcode($config))->getStream($config['content']);
        $config['logo_path'] !== '' && unlink($config['logo_path']); // 移除logo临时文件

        $tmpFilename = uniqid() . '.png';
        return $this->response->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'image/png')
            ->withHeader('content-disposition', "attachment; filename={$tmpFilename}")
            ->withHeader('content-transfer-encoding', 'binary')
            ->withBody(new SwooleStream($qrCodeString));
    }

    // 展示二维码
    #[Scene(scene: 'qrcode')]
    #[PostMapping(path: 'qrcode/show')]
    public function qrcode(ImageRequest $request): MessageInterface|ResponseInterface
    {
        $config = $this->buildQrcodeConfig($request);
        $qrCodeString = (new Qrcode($config))->getStream($config['content']);
        $config['logo_path'] !== '' && unlink($config['logo_path']); // 移除logo临时文件

        return $this->response->withHeader('Content-Type', 'image/png')
            ->withBody(new SwooleStream($qrCodeString));
    }

    // 构建二维码配置
    private function buildQrcodeConfig(ImageRequest $request): array
    {
        $logo = $request->file('logo');
        if ($logo !== null) {
            $logoPath = BASE_PATH . '/runtime/upload/' . $logo->getClientFilename();
            $logo->moveTo($logoPath);
        } else {
            $logoPath = '';
        }
        $config = $request->all();
        $config['logo_path'] = $logoPath;

        return $config;
    }
}

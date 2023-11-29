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

use App\Controller\AbstractController;
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

    // 展示二维码
    #[Scene(scene: 'qrcode')]
    #[PostMapping(path: 'qrcode/show')]
    public function qrcode(ImageRequest $request): MessageInterface|ResponseInterface
    {
        // 参数获取和处理
        $logo = $request->file('logo');
        // 对 logo 进行判断处理
        if ($logo !== null) {
            $logoPath = BASE_PATH . '/runtime/upload/' . $logo->getClientFilename();
            $logo->moveTo($logoPath);
        } else {
            $logoPath = '';
        }
        $default = [
            'size' => 300,
            'margin' => 30,
            'logo_size' => 50,
            'label_text' => '',
            'mime' => 'png',
            'foreground_color' => [0, 0, 0],
            'background_color' => [255, 255, 255],
            'content' => '',
            'is_download' => false,
        ];
        $config = $request->inputs(array_keys($default), $default);
        $config['logo_path'] = $logoPath;

        $qrcode = new Qrcode($config);
        $qrCodeString = $qrcode->getStream($config['content']);

        $logoPath !== '' && unlink($logoPath);

        if ($config['is_download']) {
            $tmpFilename = uniqid() . '.png';
            $response = $this->response->withHeader('content-description', 'File Transfer')
                ->withHeader('content-type', 'image/png')
                ->withHeader('content-disposition', "attachment; filename={$tmpFilename}")
                ->withHeader('content-transfer-encoding', 'binary')
                ->withBody(new SwooleStream($qrCodeString));
        } else {
            $response = $this->response->withHeader('Content-Type', 'image/png')
                ->withBody(new SwooleStream($qrCodeString));
        }
        return $response;
    }
}

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

namespace App\Controller\Tools;

use App\Controller\AbstractController;
use App\Lib\Image\Qrcode;
use App\Lib\Validator\ImageValidator;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: 'image')]
class ImageController extends AbstractController
{
    #[PostMapping(path: 'qrcode/show')]
    public function basicQrcode(): MessageInterface|ResponseInterface
    {
        $logo = $this->request->file('logo');
        $config = [
            'size' => intval($this->request->input('size', 300)),
            'margin' => intval($this->request->input('margin', 30)),
            'logo_size' => intval($this->request->input('logo_size', 50)),
            'label_text' => $this->request->input('label', ''),
            'mime' => $this->request->input('mime', 'png'),
            'foreground_color' => $this->request->input('foreground_color', [0, 0, 0]),
            'background_color' => $this->request->input('foreground_color', [255, 255, 255]),
            'content' => $this->request->input('content'),
        ];
        ImageValidator::validateQrcodeConfig($config);

        if ($logo !== null) {
            $realFile = BASE_PATH . '/runtime/upload/' . $logo->getClientFilename();
            $logo->moveTo($realFile);
            $config['logo_path'] = $realFile;
        }

        $qrcode = new Qrcode($config);
        $qrCodeString = $qrcode->getStream($config['content']);

        return $this->response->withHeader('Content-Type', 'image/png')
            ->withBody(new SwooleStream($qrCodeString));
    }
}

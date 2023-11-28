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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Annotation\Scene;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

#[AutoController(prefix: 'image')]
class ImageController extends AbstractController
{
    #[PostMapping(path: 'qrcode')]
    #[Scene(scene: 'qrcode')]
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
        ];
        $config = $request->inputs(array_keys($default), $default);
        $config['logo_path'] = $logoPath;

        $qrcode = new Qrcode($config);
        $qrCodeString = $qrcode->getStream($config['content']);

        return $this->response->withHeader('Content-Type', 'image/png')
            ->withBody(new SwooleStream($qrCodeString));
    }
}

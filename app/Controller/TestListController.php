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

use App\Constants\ErrorCode;
use App\Lib\Image\Barcode;
use App\Lib\Image\Captcha;
use App\Lib\Image\Qrcode;
use App\Lib\Lock\RedisLock;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: 'test')]
class TestListController extends AbstractController
{
    #[GetMapping(path: 'qrcode/stream')]
    public function qrcode(): MessageInterface|ResponseInterface
    {
        $qrCodeString = (new Qrcode())->getStream('测试内容');
        return $this->response->withHeader('Content-Type', 'image/png')
            ->withBody(new SwooleStream($qrCodeString));
    }

    #[GetMapping(path: 'qrcode/save')]
    public function saveQrcode(): array
    {
        (new Qrcode())->move('qrcode.png', '测试内容');
        return $this->result->getResult();
    }

    #[GetMapping(path: 'barcode/stream')]
    public function barcode(): MessageInterface|ResponseInterface
    {
        $barcodeString = (new Barcode())->getStream('测试内容');
        return $this->response->withHeader('Content-Type', 'image/png')
            ->withBody(new SwooleStream($barcodeString));
    }

    #[GetMapping(path: 'barcode/save')]
    public function saveBarcode(): array
    {
        (new Barcode())->move('barcode.png', '测试内容');
        return $this->result->getResult();
    }

    #[GetMapping(path: 'captcha/stream')]
    public function captcha(): MessageInterface|ResponseInterface
    {
        $clientCode = '187.091.123,111';
        $captchaString = (new Captcha())->getStream($clientCode);
        return $this->response->withHeader('Content-Type', 'image/png')
            ->withBody(new SwooleStream($captchaString));
    }

    #[GetMapping(path: 'captcha/verify')]
    public function verify(): array
    {
        $clientCode = '187.091.123,111';
        $captcha = $this->request->input('captcha', 'xxxx');
        $isPass = (new Captcha())->verify($captcha, $clientCode);
        if (! $isPass) {
            return $this->result->setErrorInfo(
                ErrorCode::CAPTCHA_ERROR,
                ErrorCode::getMessage(ErrorCode::CAPTCHA_ERROR)
            )->getResult();
        }
        return $this->result->getResult();
    }

    #[GetMapping(path: 'lock')]
    public function redisLockAsync(): array
    {
        // 不同的业务场景需要不同的实例(不可make获取该对象)
        $lock = new RedisLock('testLock', 3, 3, 'redisLockAsync');
        $result = $lock->lockFunc(function () {
            sleep(1); // 模拟业务耗时
            return ['a' => 'A'];
        });

        return $this->result->setData($result)->getResult();
    }
}

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
use App\Model\Goods;
use App\Model\Orders;
use Hyperf\DbConnection\Db;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\Stringable\Str;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

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

    #[GetMapping(path: 'lock/sync')]
    public function redisLockSync(): array
    {
        // 不同的业务场景需要不同的实例(不可make获取该对象)
        $lock = new RedisLock('testLock', 5, 3, 'redisLockSync');
        // 非阻塞: 获取不到直接返回false, 不等待持有者释放锁
        $result = $lock->lockSync(function () {
            sleep(1); // 模拟业务耗时
            return ['a' => 'A'];
        });

        return $this->result->setData($result)->getResult();
    }

    #[GetMapping(path: 'lock/async')]
    public function redisLockAsync(): array
    {
        // 不同的业务场景需要不同的实例(不可make获取该对象)
        $lock = new RedisLock('testLock_', 5, 3, 'redisLockAsync');
        // 阻塞式: 获取不到会以每200ms的频率依次尝试再次获取, 直至3秒后超时, 抛出异常
        $result = $lock->lockAsync(function () {
            sleep(1); // 模拟业务耗时
            return ['a' => 'A'];
        });

        return $this->result->setData($result)->getResult();
    }

    #[GetMapping(path: 'pessimism/write/lock')]
    public function pessimismWriteLock(): array
    {
        $gid = $this->request->input('gid', 1);
        $num = $this->request->input('num', 1);
        try {
            Db::beginTransaction();
            /** @var Goods $goodInfo */
            //  $goodInfo = Goods::where(['id' => $gid])->sharedLock()->firstOrFail();
            $goodInfo = Goods::where(['id' => $gid])->lockForUpdate()->firstOrFail();
            sleep(5); // 模拟长事务
            if ($goodInfo->stock > 0 && $goodInfo->stock >= $num) {
                (new Orders([
                    'gid' => $goodInfo->id,
                    'order_id' => Str::random() . uniqid(),
                    'number' => $num,
                    'money' => $goodInfo->price * $num,
                    'customer' => 'Jerry',
                ]))->save();

                $goodInfo->stock = $goodInfo->stock - $num;
                $goodInfo->save();

                Db::commit();
                return $this->result->getResult();
            }

            Db::rollBack();
            return $this->result->setErrorInfo(
                ErrorCode::STOCK_ERR,
                ErrorCode::getMessage(ErrorCode::STOCK_ERR, [$goodInfo->name])
            )->getResult();
        } catch (Throwable $e) {
            Db::rollBack();
            return $this->result->setErrorInfo($e->getCode(), $e->getMessage())->getResult();
        }
    }
}

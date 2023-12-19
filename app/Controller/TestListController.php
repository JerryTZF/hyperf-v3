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
use App\Job\CreateOrderJob;
use App\Job\DemoJob;
use App\Lib\Cache\Cache;
use App\Lib\Encrypt\Aes;
use App\Lib\Encrypt\AesWithPHPSeclib;
use App\Lib\Encrypt\Rc4WithPHPSecLib;
use App\Lib\Encrypt\RsaWithPHPSeclib;
use App\Lib\File\FileSystem;
use App\Lib\GuzzleHttp\GuzzleFactory;
use App\Lib\Lock\RedisLock;
use App\Lib\Log\Log;
use App\Lib\Office\ExportCsvHandler;
use App\Lib\Office\ExportExcelHandler;
use App\Lib\RedisQueue\RedisQueueFactory;
use App\Model\Orders;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Coroutine\Coroutine;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class TestListController extends AbstractController
{
    #[GetMapping(path: 'lock/queue')]
    public function redisQueueLock(): array
    {
        $queueParams = [
            'gid' => $this->request->input('gid', 1),
            'num' => $this->request->input('num', 1),
        ];
        $client = '121.1.21.331' . uniqid();
        $queueInstance = RedisQueueFactory::getQueueInstance('limit-queue');
        $isPushSuccess = $queueInstance->push(new CreateOrderJob($client, $queueParams));
        if (! $isPushSuccess) {
            return $this->result->setErrorInfo(
                ErrorCode::QUEUE_PUSH_ERR,
                ErrorCode::getMessage(ErrorCode::QUEUE_PUSH_ERR, [CreateOrderJob::class])
            )->getResult();
        }

        return $this->result->getResult();
    }

    #[GetMapping(path: 'office/excel/save')]
    public function saveExcel(): array
    {
        $lock = new RedisLock('save_excel', 3, 3, 'saveExcel');
        // 制作过程中因为是对对象操作，所以不应该并行操作同一对象,也减少内存使用
        $file = $lock->lockAsync(function () {
            $excelHandler = new ExportExcelHandler();
            $excelHandler->setHeaders([
                'ID', '商品ID', '订单号', '购买数量', '金额', '客户', '创建时间', '修改时间',
            ]);
            Orders::query()->orderBy('id', 'DESC')
                ->chunk(20, function ($records) use ($excelHandler) {
                    $excelHandler->setData($records->toArray());
                });
            return $excelHandler->saveToLocal('测试导出');
        });
        return $this->result->setData(['file_path' => $file])->getResult();
    }

    #[GetMapping(path: 'office/csv/download')]
    public function downloadCsv()
    {
        $lock = new RedisLock('export_csv', 3, 3, 'downloadCsv');
        return $lock->lockAsync(function () {
            $csvHandler = new ExportCsvHandler();
            $csvHandler->setHeaders([
                'ID', '商品ID', '订单号', '购买数量', '金额', '客户', '创建时间', '修改时间',
            ]);
            Orders::query()->orderBy('id', 'DESC')
                ->chunk(20, function ($records) use ($csvHandler) {
                    $csvHandler->setData($records->toArray());
                });
            return $csvHandler->saveToBrowser('CSV测试导出');
        });
    }

    #[GetMapping(path: 'office/csv/save')]
    public function saveCsv(): array
    {
        $lock = new RedisLock('save_csv', 3, 3, 'saveCsv');
        $file = $lock->lockAsync(function () {
            $csvHandler = new ExportCsvHandler();
            $csvHandler->setHeaders([
                'ID', '商品ID', '订单号', '购买数量', '金额', '客户', '创建时间', '修改时间',
            ]);
            Orders::query()->orderBy('id', 'DESC')
                ->chunk(20, function ($records) use ($csvHandler) {
                    $csvHandler->setData($records->toArray());
                });
            return $csvHandler->saveToLocal('Csv测试导出');
        });
        return $this->result->setData(['file_path' => $file])->getResult();
    }

    #[GetMapping(path: 'aes')]
    public function aes(): array
    {
        // ecb 加密解密
        $data = ['key' => 'Aes', 'msg' => '待加密数据'];
        $key = 'KOQ19sd3_1kaseq/';
        $iv = 'hello world';
        $ecbEncryptHex = Aes::ecbEncryptHex($data, $key, 'Aes-128-ECB');
        $ecbDecryptHex = Aes::ecbDecryptHex($ecbEncryptHex, $key, 'Aes-128-ECB');
        var_dump($ecbEncryptHex, $ecbDecryptHex);
        $ecbEncryptBase64 = Aes::ecbEncryptBase64($data, $key, 'Aes-128-ECB');
        $ecbDecryptBase64 = Aes::ecbDecryptBase64($ecbEncryptBase64, $key, 'Aes-128-ECB');
        var_dump($ecbEncryptBase64, $ecbDecryptBase64);

        // cbc 加解密
        $cbcEncryptHex = Aes::cbcEncryptHex($data, $key, $iv, 'Aes-128-CBC');
        $cbcDecryptHex = Aes::cbcDecryptHex($cbcEncryptHex, $key, $iv, 'Aes-128-CBC');
        var_dump($cbcEncryptHex, $cbcDecryptHex);
        $cbcEncryptBase64 = Aes::cbcEncryptBase64($data, $key, $iv, 'Aes-128-CBC');
        $cbcDecryptBase64 = Aes::cbcDecryptBase64($cbcEncryptBase64, $key, $iv, 'Aes-128-CBC');
        var_dump($cbcEncryptBase64, $cbcDecryptBase64);

        return $this->result->getResult();
    }

    #[GetMapping(path: 'seclib/aes')]
    public function seclibAes(): array
    {
        $constructEcb = [
            'ecb', 128, 'KOQ19sd3_1kaseq/', [],
        ];
        $constructCbc = [
            'cbc', 128, 'KOQ19sd3_1kaseq/', ['iv' => 'hello world'],
        ];
        $data = ['key' => 'Aes', 'msg' => '待加密数据'];
        $aesInstance = new AesWithPHPSeclib(...$constructCbc);
        $ecbEncryptHex = $aesInstance->encryptHex($data);
        $ecbDecryptHex = $aesInstance->decryptHex($ecbEncryptHex);
        var_dump($ecbEncryptHex, $ecbDecryptHex);
        $ecbEncryptBase64 = $aesInstance->encryptBase64($data);
        $ecbDecryptBase64 = $aesInstance->decryptBase64($ecbEncryptBase64);
        var_dump($ecbEncryptBase64, $ecbDecryptBase64);

        return $this->result->getResult();
    }

    #[GetMapping(path: 'seclib/rsa')]
    public function seclibRsa(): array
    {
        // 默认属性, 具体参考封装类
        $rsaInstance = new RsaWithPHPSeclib();

        // 公钥加密
        $encryptData = $rsaInstance->publicKeyEncrypt('hello world');
        // 私钥解密
        $decryptData = $rsaInstance->privateKeyDecrypt($encryptData);

        // 私钥加签
        $signature = $rsaInstance->privateKeySign('hello world');
        // 公钥验签
        $verifyResult = $rsaInstance->publicKeyVerifySign('hello world', $signature);

        return $this->result->setData([
            'public_key_to_encrypt_data' => $encryptData,
            'private_key_to_decrypt_data' => $decryptData,
            'private_key_to_sign_data' => $signature,
            'public_key_to_verify_sign' => $verifyResult,
        ])->getResult();
    }

    #[GetMapping(path: 'rc4')]
    public function rc4(): array
    {
        $rc4Instance = new Rc4WithPHPSecLib('hello world');
        $body = ['a' => 'A'];

        $encrypt = $rc4Instance->encrypt($body);
        $decrypt = $rc4Instance->decrypt($encrypt);

        $encrypt_ = $rc4Instance->encryptNative($body);
        $decrypt_ = $rc4Instance->decryptNative($encrypt_);

        return $this->result->setData([
            'encrypt' => $encrypt,
            'decrypt' => $decrypt,
            'encrypt_' => $encrypt_,
            'decrypt_' => $decrypt_,
        ])->getResult();
    }

    #[GetMapping(path: 'file')]
    public function file(): ResponseInterface
    {
        return (new FileSystem())->download('/img/20210430171345.png');
    }

    #[GetMapping(path: 'simple/cache')]
    public function simpleCache(): array
    {
        try {
            $cache = Cache::getInstance();
            // 一般对于缓存,Key里面会加入一些变量,那么可以将Key写入枚举类
            $key = sprintf('%s:%s', 'YOUR_APPID', 'USER_ID');
            // 一次写入单个缓存
            $cache->set($key, ['a' => 'b'], 300);
            // 读取单个缓存
            $cacheData = $cache->get($key, '');
            // 一次写入多个缓存(具有原子性)
            $cache->setMultiple(['key1' => 'value1', 'key2' => 'value2'], 300);
            // 一次读取多个缓存
            $multipleData = $cache->getMultiple(['key1', 'key2'], []);

            // 清除所有的key
            $cache->clear();
        } catch (Throwable $e) {
            return $this->result->setErrorInfo($e->getCode(), $e->getMessage())->getResult();
        }

        return $this->result->setData([
            'single' => $cacheData,
            'multiple' => $multipleData,
        ])->getResult();
    }

    #[GetMapping(path: 'inject/cache')]
    #[Cacheable(prefix: 'test_api', value: '#{param1}_#{param2}', ttl: 600, listener: 'UPDATE_TEST')]
    public function getFromCache(string $param1 = 'hello', string $param2 = 'world'): array
    {
        Log::stdout()->info("I'm Running...");
        return $this->result->setData(['param1' => $param1, 'param2' => $param2])->getResult();
    }

    #[GetMapping(path: 'flush/cache')]
    public function flushCache(): array
    {
        // 在指定时机刷新监听 'UPDATE_TEST' 时间的缓存.
        (new Cache())->flush('UPDATE_TEST', [
            'param1' => 'hello',
            'param2' => 'world',
        ]);

        return $this->result->getResult();
    }

    #[GetMapping(path: 'queue/run')]
    public function queueStop(): array
    {
        $factory = RedisQueueFactory::getQueueInstance('redis-queue');
        Coroutine::create(function () use ($factory) {
            $factory->push(new DemoJob(microtime() . uniqid(), []));
        });
        return $this->result->getResult();
    }

    #[GetMapping(path: 'queue/safe_push')]
    public function safePushMessage(): array
    {
        for ($i = 10; --$i;) {
            Coroutine::create(function () use ($i) {
                $job = new DemoJob((string) $i, []);
                RedisQueueFactory::safePush($job, 'redis-queue', 0);
            });
        }

        return $this->result->getResult();
    }

    #[GetMapping(path: 'guzzle/test')]
    public function guzzle(): array
    {
        $client = GuzzleFactory::getCoroutineGuzzleClient();
        var_dump($client->get('http://www.baidu.com')->getBody()->getContents());
        return $this->result->getResult();
    }

    #[GetMapping(path: 'rate/limit')]
    #[RateLimit(create: 10, consume: 5, capacity: 50)]
    public function rateLimit(): array
    {
        return $this->result->setData([
            'QPS' => 5,
            'CREATE TOKEN PER SECOND' => 10,
            'CAPACITY' => 50,
        ])->getResult();
    }
}

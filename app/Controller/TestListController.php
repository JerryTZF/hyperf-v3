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

use App\Job\DemoJob;
use App\Lib\Cache\Cache;
use App\Lib\GuzzleHttp\GuzzleFactory;
use App\Lib\Log\Log;
use App\Lib\RedisQueue\RedisQueueFactory;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Coroutine\Coroutine;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Throwable;

class TestListController extends AbstractController
{
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

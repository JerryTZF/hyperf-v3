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

namespace App\Lib\GuzzleHttp;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;

use function Hyperf\Support\make;

class GuzzleFactory
{
    /**
     * 获取带有连接池的协程的guzzle客户端.
     * @explain make 从di中获取单例.
     * @see https://docs.guzzlephp.org/en/stable/
     */
    public static function getCoroutineGuzzleClient(array $options = []): Client
    {
        [$handler, $retry, $config] = [
            make(PoolHandler::class, ['option' => ['max_connections' => 50]]),
            make(RetryMiddleware::class, ['retries' => 1, 'delay' => 10]),
            [],
        ];
        $stack = HandlerStack::create($handler);
        $stack->push($retry->getMiddleware(), 'retry');

        $config['handler'] = $options['handler'] ?? $stack;
        $config = array_merge($config, $options);
        return make(Client::class, ['config' => $config]);
    }
}

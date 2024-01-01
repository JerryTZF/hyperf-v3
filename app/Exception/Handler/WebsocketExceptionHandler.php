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

namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\WebSocketServer\Exception\InvalidMethodException;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Hyperf\WebSocketServer\Exception\WebSocketMessageException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

// 参见: vendor/hyperf/websocket-server/src/Exception/Handler/WebSocketExceptionHandler.php
class WebsocketExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): MessageInterface|ResponseInterface
    {
        $this->stopPropagation();

        if ($throwable instanceof HttpException) {
            $response = $response->withStatus($throwable->getStatusCode());
        }
        var_dump($throwable->getMessage());
        $stream = new SwooleStream($throwable->getMessage());
        return $response->withBody($stream);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
        return $throwable instanceof WebSocketHandeShakeException
            || $throwable instanceof InvalidMethodException
            || $throwable instanceof WebSocketMessageException;
    }
}

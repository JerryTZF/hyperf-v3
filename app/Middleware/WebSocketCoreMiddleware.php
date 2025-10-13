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

namespace App\Middleware;

use Hyperf\Context\Context;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Hyperf\WebSocketServer\Security;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// 详情参见: vendor/hyperf/websocket-server/src/CoreMiddleware.php
class WebSocketCoreMiddleware extends CoreMiddleware
{
    public const HANDLER_NAME = 'class';

    /**
     * 路由错误, 直接在握手回调中断开连接.
     * Handle the response when NOT found.
     */
    public function handleNotFound(ServerRequestInterface $request): MessageInterface
    {
        return $this->response()
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Description', 'Route Error')
            ->withStatus(404)->withBody(new SwooleStream(''));
    }

    /**
     * 如果路由正确, 则协议升级处理.
     * Handle the response when found.
     */
    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request): ResponseInterface
    {
        [$controller] = $this->prepareHandler($dispatched->handler->callback);
        if (! $this->container->has($controller)) {
            throw new WebSocketHandeShakeException('Router not exist.');
        }

        /** @var Response $response */
        $response = Context::get(ResponseInterface::class);

        $security = $this->container->get(Security::class);

        $key = $request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
        $response = $response->withStatus(101)->withHeaders($security->handshakeHeaders($key));
        if ($wsProtocol = $request->getHeaderLine(Security::SEC_WEBSOCKET_PROTOCOL)) {
            $response = $response->withHeader(Security::SEC_WEBSOCKET_PROTOCOL, $wsProtocol);
        }

        return $response->withAttribute(self::HANDLER_NAME, $controller);
    }
}

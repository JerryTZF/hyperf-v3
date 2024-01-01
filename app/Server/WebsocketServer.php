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

namespace App\Server;

use App\Middleware\WebSocketCoreMiddleware;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Constant;
use Hyperf\Engine\Contract\WebSocket\WebSocketInterface;
use Hyperf\Engine\WebSocket\WebSocket;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Support\SafeCaller;
use Hyperf\WebSocketServer\Collector\FdCollector;
use Hyperf\WebSocketServer\Context as WsContext;
use Hyperf\WebSocketServer\CoreMiddleware;
use Hyperf\WebSocketServer\Exception\Handler\WebSocketExceptionHandler;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Hyperf\WebSocketServer\Security;
use Hyperf\WebSocketServer\Server;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * 覆写 websocket server. 原因:
 * 1. 自定义回调事件异常处理器.
 * 2. 自定义CoreMiddleware中间件(handleFound() && handleNotFound()).
 * Class WebsocketServer.
 */
class WebsocketServer extends Server
{
    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $this->coreMiddleware = new WebSocketCoreMiddleware($this->container, $serverName);

        $config = $this->container->get(ConfigInterface::class);
        // 加载 websocket middleware
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        // 加载 websocket exception handler
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, [
            WebSocketExceptionHandler::class,
        ]);
    }

    public function onHandShake($request, $response): void
    {
        try {
            CoordinatorManager::until(Constants::WORKER_START)->yield();
            $fd = $this->getFd($response);
            Context::set(WsContext::FD, $fd);
            $security = $this->container->get(Security::class);

            $psr7Response = $this->initResponse();
            $psr7Request = $this->initRequest($request);

            $this->logger->debug(sprintf('WebSocket: fd[%d] start a handshake request.', $fd));

            $key = $psr7Request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
            if ($security->isInvalidSecurityKey($key)) {
                throw new WebSocketHandeShakeException('sec-websocket-key is invalid!');
            }

            $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
            $middlewares = $this->middlewares;
            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);
            if ($dispatched->isFound()) {
                $registeredMiddlewares = MiddlewareManager::get($this->serverName, $dispatched->handler->route, $psr7Request->getMethod());
                $middlewares = array_merge($middlewares, $registeredMiddlewares);
            }

            /** @var Response $psr7Response */
            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);
            // 中间件返回的状态码
            $httpCode = $psr7Response->getStatusCode();
            if ($httpCode !== 101) {
                $middlewareResponseBody = $psr7Response->getBody()->getContents();
                $middlewareResponseBody = json_decode($middlewareResponseBody, true) ?? [];
                $this->logger->debug($middlewareResponseBody['msg']);
                return;
            }
            $class = $psr7Response->getAttribute(CoreMiddleware::HANDLER_NAME);
            // 未找到路由会得不到该Attr, 原因是路由错误.
            // 参见: app/Middleware/WebSocketCoreMiddleware.php
            if (empty($class)) {
                $this->logger->warning('WebSocket hande shake failed, because the class does not exists (Maybe route error).');
                return;
            }

            FdCollector::set($fd, $class);
            $server = $this->getServer();
            if (Constant::isCoroutineServer($server)) {
                $upgrade = new WebSocket($response, $request);

                $this->getSender()->setResponse($fd, $response);
                $this->deferOnOpen($request, $class, $response, $fd);

                $upgrade->on(WebSocketInterface::ON_MESSAGE, $this->getOnMessageCallback());
                $upgrade->on(WebSocketInterface::ON_CLOSE, $this->getOnCloseCallback());
                $upgrade->start();
            } else {
                $this->deferOnOpen($request, $class, $server, $fd);
            }
        } catch (Throwable $throwable) {
            // Delegate the exception to exception handler.
            $psr7Response = $this->container->get(SafeCaller::class)->call(function () use ($throwable) {
                return $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
            }, static function () {
                return (new Psr7Response())->withStatus(400);
            });

            isset($fd) && FdCollector::del($fd);
            isset($fd) && WsContext::release($fd);
        } finally {
            isset($fd) && $this->getSender()->setResponse($fd, null);
            // Send the Response to client.
            if (isset($psr7Response) && $psr7Response instanceof ResponseInterface) {
                $this->responseEmitter->emit($psr7Response, $response, true);
            }
        }
    }
}

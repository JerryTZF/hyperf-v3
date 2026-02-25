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
 * 3. 根据自己需求可以修改各个回调事件的逻辑.
 * Class WebsocketServer.
 */
class WebsocketServer extends Server
{
    // 初始化协议升级中间件 && 加载异常处理器
    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        // 注册自定义Core中间件
        $this->coreMiddleware = new WebSocketCoreMiddleware($this->container, $serverName);

        $config = $this->container->get(ConfigInterface::class);
        // 加载 websocket middleware
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        // 加载 websocket exception handler
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, [
            WebSocketExceptionHandler::class,
        ]);
    }

    // 握手回调函数
    public function onHandShake($request, $response): void
    {
        $fd = null;
        $psr7Response = null;

        try {
            // 等待工作进程启动完成
            CoordinatorManager::until(Constants::WORKER_START)->yield();

            // 获取连接标识符
            $fd = $this->getFd($response);
            Context::set(WsContext::FD, $fd);

            $this->logger->debug(sprintf('WebSocket: fd[%d] start a handshake request.', $fd));

            // 初始化安全检查组件
            $security = $this->container->get(Security::class);

            // 初始化 PSR-7 请求和响应对象
            $psr7Response = $this->initResponse();
            $psr7Request = $this->initRequest($request);

            // 验证 WebSocket 密钥
            $key = $psr7Request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
            if ($security->isInvalidSecurityKey($key)) {
                $this->logger->warning(sprintf('WebSocket handshake failed: invalid sec-websocket-key for fd[%d]', $fd));
                throw new WebSocketHandeShakeException('sec-websocket-key is invalid!');
            }

            // 路由分发
            $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
            $middlewares = $this->middlewares;

            /** @var Dispatched $dispatched */
            $dispatched = $psr7Request->getAttribute(Dispatched::class);
            if ($dispatched->isFound()) {
                $registeredMiddlewares = MiddlewareManager::get($this->serverName, $dispatched->handler->route, $psr7Request->getMethod());
                $middlewares = array_merge($middlewares, $registeredMiddlewares);
            }

            // 执行中间件链
            /** @var Response $psr7Response */
            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);

            // 检查协议升级结果
            $httpCode = $psr7Response->getStatusCode();
            if ($httpCode !== 101) {
                // 协议升级失败，记录详细信息
                $middlewareResponseBody = $psr7Response->getBody()->getContents();
                $middlewareResponseBody = json_decode($middlewareResponseBody, true) ?? [];
                $errorMsg = $middlewareResponseBody['msg'] ?? 'Unknown middleware error';
                $this->logger->info(sprintf('WebSocket handshake rejected for fd[%d]: %s', $fd, $errorMsg));
                return;
            }

            // 获取处理器类名
            $class = $psr7Response->getAttribute(CoreMiddleware::HANDLER_NAME);
            if (empty($class)) {
                $this->logger->warning(sprintf('WebSocket handshake failed for fd[%d]: handler class not found (route error)', $fd));
                return;
            }

            // 注册连接信息
            FdCollector::set($fd, $class);

            // 获取服务器实例并建立连接
            $server = $this->getServer();
            if (Constant::isCoroutineServer($server)) {
                // 协程服务器模式
                $upgrade = new WebSocket($response, $request);
                $this->getSender()->setResponse($fd, $response);

                // 延迟执行 onOpen 回调
                $this->deferOnOpen($request, $class, $response, $fd);

                // 注册消息和关闭回调
                $upgrade->on(WebSocketInterface::ON_MESSAGE, $this->getOnMessageCallback());
                $upgrade->on(WebSocketInterface::ON_CLOSE, $this->getOnCloseCallback());

                // 启动 WebSocket 连接
                $upgrade->start();
            } else {
                // 传统服务器模式
                $this->deferOnOpen($request, $class, $server, $fd);
            }

            $this->logger->info(sprintf('WebSocket handshake successful for fd[%d], handler: %s', $fd, $class));
        } catch (Throwable $throwable) {
            // 异常处理
            $this->logger->error(sprintf(
                'WebSocket handshake error for fd[%s]: %s in %s:%d',
                $fd ?? 'unknown',
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine()
            ));

            // 清理资源
            $this->cleanupConnection($fd);

            // 使用安全调用处理异常
            $psr7Response = $this->container->get(SafeCaller::class)->call(
                function () use ($throwable) {
                    return $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
                },
                static function () {
                    return (new Psr7Response())->withStatus(400);
                }
            );
        } finally {
            // 清理响应关联
            if ($fd !== null) {
                $this->getSender()->setResponse($fd, null);
            }

            // 发送响应给客户端
            if ($psr7Response instanceof ResponseInterface) {
                $this->responseEmitter->emit($psr7Response, $response, true);
            }
        }
    }

    /**
     * 清理 WebSocket 连接资源.
     * @param null|int $fd 连接标识符
     */
    private function cleanupConnection(?int $fd): void
    {
        if ($fd === null) {
            return;
        }

        try {
            FdCollector::del($fd);
            WsContext::release($fd);
            $this->logger->debug(sprintf('WebSocket connection resources cleaned for fd[%d]', $fd));
        } catch (Throwable $e) {
            $this->logger->error(sprintf(
                'Error cleaning WebSocket connection resources for fd[%d]: %s',
                $fd,
                $e->getMessage()
            ));
        }
    }
}

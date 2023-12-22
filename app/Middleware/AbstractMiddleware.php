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

use App\Constants\ErrorCode;
use App\Constants\SystemCode;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface as ContractRequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as ContractResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AbstractMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected ContractRequestInterface $request;

    #[Inject]
    protected ContractResponseInterface $response;

    #[Inject]
    protected ContainerInterface $container;

    /**
     * 中间件逻辑重写.
     * @param ServerRequestInterface $request 请求
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface 响应
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

    /**
     * 返回重定向.
     * @param string $toUrl 跳转地址
     * @return ResponseInterface 响应
     */
    public function buildRedirectResponse(string $toUrl): ResponseInterface
    {
        return $this->response->redirect($toUrl);
    }

    /**
     * 构建异常返回.
     * @param int $errorCode 错误码
     * @return MessageInterface|ResponseInterface 响应
     */
    public function buildErrorResponse(int $errorCode): MessageInterface|ResponseInterface
    {
        $error = [
            'code' => $errorCode,
            'msg' => ErrorCode::getMessage($errorCode) ?: SystemCode::getMessage($errorCode),
            'status' => false,
            'data' => [],
        ];
        $response = Context::get(ResponseInterface::class);
        $response = $response->withStatus(401)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new SwooleStream(json_encode($error, JSON_UNESCAPED_UNICODE)));
        Context::set(ResponseInterface::class, $response);
        return $response;
    }
}

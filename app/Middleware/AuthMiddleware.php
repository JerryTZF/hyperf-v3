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
use App\Service\RoleService;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected RoleService $service;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 登录相关不校验
        if ($this->request->is('login/*')) {
            return $handler->handle($request);
        }

        $payload = $this->request->getAttribute('jwt');
        $uid = $payload['data']['uid'];
        $rid = $payload['data']['rid'];
        $auths = $this->service->getAuthsByRoleIds($rid);
        [$authInfos, $nodeInfos] = [$auths['auth_list'], $auths['node_list']];

        $routes = array_column($authInfos, 'route');
        if (! in_array($this->request->getPathInfo(), $routes)) {
            return $this->buildErrorResponse(ErrorCode::NO_AUTH);
        }

        return $handler->handle($request);
    }

    /**
     * 构建异常返回.
     * @param int $errorCode 错误码
     * @return MessageInterface|ResponseInterface 响应
     */
    private function buildErrorResponse(int $errorCode): MessageInterface|ResponseInterface
    {
        $error = [
            'code' => $errorCode,
            'msg' => ErrorCode::getMessage($errorCode),
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

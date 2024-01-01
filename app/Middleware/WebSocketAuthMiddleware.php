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
use App\Lib\Jwt\Jwt;
use Hyperf\Stringable\Str;
use Hyperf\WebSocketServer\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

// websocket auth check middleware
class WebSocketAuthMiddleware extends AbstractMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isOpenCheck = \Hyperf\Support\env('JWT_OPEN', false);
        // 不开启验证直接通过
        if (! $isOpenCheck) {
            return $handler->handle($request);
        }
        $authorization = $request->hasHeader('authorization') ? $request->getHeaderLine('authorization') : '';
        $route = $request->getUri()->getPath();
        $whiteRouteList = [
            '/wss/demo',
        ];

        // 不在白名单 || jwt为空
        if (! in_array($route, $whiteRouteList) || $authorization === '') {
            return $this->buildErrorResponse(ErrorCode::JWT_EMPTY_ERR);
        }

        try {
            $jwt = Str::startsWith($authorization, 'Bearer') ? Str::after($authorization, 'Bearer ') : $authorization;
            // jwt 解析失败
            $explainJwt = Jwt::explainJwt($jwt);
        } catch (Throwable $e) {
            return $this->buildErrorResponse(ErrorCode::DO_JWT_FAIL);
        }

        Context::set('jwt', $explainJwt);
        return $handler->handle($request);
    }
}

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
use App\Model\Users;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// 授权验证
class AccreditMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected RequestInterface $request;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$response, $authorization, $isAuthPath, $isOpenCheck] = [
            Context::get(ResponseInterface::class),
            $request->hasHeader('authorization') ? $request->getHeaderLine('authorization') : '',
            $this->request->is('auth/*'),
            \Hyperf\Support\env('JWT_OPEN', false),
        ];

        // 不开启验证 && 是权限相关理由 直接通过
        if (! $isOpenCheck || $isAuthPath) {
            return $handler->handle($request);
        }

        // 非权限路由且不存在jwt
        if ($authorization === '') {
            $error = ['code' => ErrorCode::JWT_EMPTY_ERR, 'msg' => ErrorCode::getMessage(ErrorCode::JWT_EMPTY_ERR), 'status' => false, 'data' => []];
            $response = $response->withStatus(401)->withHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(json_encode($error, JSON_UNESCAPED_UNICODE)));
            Context::set(ResponseInterface::class, $response);
            return $response;
        }
        $jwt = Str::startsWith($authorization, 'Bearer') ? Str::after($authorization, 'Bearer ') : $authorization;
        $originalData = Jwt::explainJwt($jwt); // 解析过程中的异常, 会被 JwtExceptionHandler 捕获, 这里无需处理

        // token是否被主动失效
        $storageJwt = Users::query()->where(['id' => $originalData['data']['uid']])->value('jwt_token');
        if ($storageJwt !== $jwt) {
            $error = ['code' => ErrorCode::DO_JWT_FAIL, 'msg' => ErrorCode::getMessage(ErrorCode::DO_JWT_FAIL), 'status' => false, 'data' => []];
            $response = $response->withStatus(401)->withHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(json_encode($error, JSON_UNESCAPED_UNICODE)));
            Context::set(ResponseInterface::class, $response);
            return $response;
        }

        // TODO 可以根据 payload 的数据进行其他的判断操作. 这里直接将 payload 向下游传递.
        $request = Context::set(ServerRequestInterface::class, $request->withAttribute('jwt', $originalData));

        return $handler->handle($request);
    }
}

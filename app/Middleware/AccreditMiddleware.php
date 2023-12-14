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
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AccreditMiddleware extends AbstractMiddleware
{
    /**
     * jwt验证.
     * 原则上只检测jwt相关, 权限等在AuthMiddleware中间件实现.
     * @param ServerRequestInterface $request 请求类
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface 响应
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$selfCalled, $authorization, $isLoginPath, $isOpenCheck] = [
            $request->hasHeader('x-self-called'),
            $request->hasHeader('authorization') ? $request->getHeaderLine('authorization') : '',
            $this->request->is('login/*'),
            \Hyperf\Support\env('JWT_OPEN', false),
        ];

        // 不开启验证 || 是权限相关理由 || 系统调用
        if (! $isOpenCheck || $isLoginPath || $selfCalled) {
            return $handler->handle($request);
        }

        // 非权限路由且不存在jwt
        if ($authorization === '') {
            return $this->buildErrorResponse(ErrorCode::JWT_EMPTY_ERR);
        }
        $jwt = Str::startsWith($authorization, 'Bearer') ? Str::after($authorization, 'Bearer ') : $authorization;
        $originalData = Jwt::explainJwt($jwt); // 解析过程中的异常, 会被 JwtExceptionHandler 捕获, 这里无需处理

        // JWT是否被主动失效
        $uid = $originalData['data']['uid'] ?? 0;
        $storageJwt = Users::query()->where(['id' => $uid, 'status' => Users::STATUS_ACTIVE])->value('jwt_token');
        if ($storageJwt !== $jwt) {
            return $this->buildErrorResponse(ErrorCode::DO_JWT_FAIL);
        }

        // TODO 可以根据 payload 的数据进行其他的判断操作. 这里直接将 payload 向下游传递.
        $request = Context::set(ServerRequestInterface::class, $request->withAttribute('jwt', $originalData));

        return $handler->handle($request);
    }
}

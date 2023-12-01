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

use App\Constants\SystemCode;
use App\Lib\Jwt\Jwt;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AccreditMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $request->hasHeader('authorization')) {
            $response = Context::get(ResponseInterface::class);
            $response = $response->withStatus(401)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(json_encode([
                    'code' => SystemCode::JWT_ERROR,
                    'msg' => SystemCode::getMessage(SystemCode::JWT_ERROR, ['jwt 缺失']),
                    'status' => false,
                    'data' => [],
                ], JSON_UNESCAPED_UNICODE)));
            Context::set(ResponseInterface::class, $response);
            return $response;
        }
        $jwt = $request->getHeaderLine('authorization');
        $jwt = Str::startsWith($jwt, 'Bearer') ? Str::after($jwt, 'Bearer ') : $jwt;
        $originalData = Jwt::explainJwt($jwt); // 解析过程中的异常, 会被 JwtExceptionHandler 捕获, 这里无需处理

        // TODO 可以根据 payload 的数据进行其他的判断操作. 这里直接将 payload 向下游传递.
        $request = Context::set(ServerRequestInterface::class, $request->withAttribute('jwt', $originalData));

        return $handler->handle($request);
    }
}

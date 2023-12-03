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

use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Request;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface|RequestInterface|Request $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 登录相关不校验
        $url = $request->path();
        var_dump($url);

        return $handler->handle($request);
    }
}

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

// 进入维护模式时, 所有的请求均不可访问
use App\Constants\ConstCode;
use App\Constants\SystemCode;
use Hyperf\Cache\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\InvalidArgumentException;

class MaintenanceMiddleware extends AbstractMiddleware
{
    /**
     * 是否开启维护模式.
     * @param ServerRequestInterface $request 请求类
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface 响应
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     * @throws InvalidArgumentException 异常
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cache = $this->container->get(Cache::class);
        $isFixMode = $cache->get(ConstCode::FIX_MODE, false);
        if ($isFixMode !== false) {
            return $this->buildErrorResponse(SystemCode::FIX_MODE);
        }
        return $handler->handle($request);
    }
}

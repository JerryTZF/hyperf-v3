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

use App\Model\ShortChain;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Response;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShortChainMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected RequestInterface $request;

    /**
     * 短链中间件.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->request->getPathInfo();
        // 判断是不是短链路由
        if (substr_count($route, '/') >= 2) {
            return $handler->handle($request);
        }
        $hashCode = Str::after($route, '/');
        /** @var ShortChain $chain */
        $chain = ShortChain::query()
            ->where(['hash_code' => $hashCode, 'status' => ShortChain::STATUS_ACTIVE])
            ->first();
        if ($chain === null) {
            return $handler->handle($request);
        }

        return $this->buildRedirectResponse($chain->url);
    }

    /**
     * 返回短链重定向.
     * @param string $toUrl 跳转地址
     * @return ResponseInterface 响应
     * @throws ContainerExceptionInterface 异常
     * @throws NotFoundExceptionInterface 异常
     */
    private function buildRedirectResponse(string $toUrl): ResponseInterface
    {
        $response = ApplicationContext::getContainer()->get(Response::class);
        return $response->redirect($toUrl);
    }
}

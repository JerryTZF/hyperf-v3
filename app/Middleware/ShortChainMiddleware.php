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
use App\Model\ShortChain;
use Carbon\Carbon;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShortChainMiddleware extends AbstractMiddleware
{
    /**
     * 短链中间件.
     * @param ServerRequestInterface $request 请求类
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface 响应
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

        [$toUrl, $expireAt] = [$chain->url, $chain->expire_at];
        // 过期短链
        if (Carbon::createFromFormat('Y-m-d H:i:s', $expireAt) < time()) {
            return $this->buildErrorResponse(ErrorCode::SHORT_CHAIN_EXPIRED);
        }

        return $this->buildRedirectResponse($toUrl);
    }
}

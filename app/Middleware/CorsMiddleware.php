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

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware extends AbstractMiddleware
{
    #[Inject]
    protected ConfigInterface $config;

    /**
     * 跨域处理中间件.
     * @param ServerRequestInterface $request 请求对象
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface 响应对象
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 获取 CORS 配置
        $corsConfig = $this->getCorsConfig();

        // 设置响应头
        $response = Context::get(ResponseInterface::class);

        // 设置 Access-Control-Allow-Origin
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigin = $this->getAllowedOrigin($origin, $corsConfig);
        if ($allowedOrigin) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $allowedOrigin);
        }

        // 设置其他 CORS 头部
        $response = $response->withHeader('Access-Control-Allow-Credentials', $corsConfig['allow_credentials'] ? 'true' : 'false');
        $response = $response->withHeader('Access-Control-Allow-Methods', implode(',', $corsConfig['allow_methods']));
        $response = $response->withHeader('Access-Control-Allow-Headers', implode(',', $corsConfig['allow_headers']));
        $response = $response->withHeader('Access-Control-Max-Age', (string) $corsConfig['max_age']);
        $response = $response->withHeader('Access-Control-Expose-Headers', implode(',', $corsConfig['expose_headers']));

        Context::set(ResponseInterface::class, $response);

        // 处理预检请求(OPTIONS)
        if ($request->getMethod() === 'OPTIONS') {
            return $response->withStatus(204);
        }

        // 继续处理正常请求
        return $handler->handle($request);
    }

    /**
     * 获取 CORS 配置.
     * @return array CORS 配置数组
     */
    private function getCorsConfig(): array
    {
        return $this->config->get('cors', [
            'allow_origins' => ['*'],
            'allow_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allow_headers' => [
                'DNT',
                'Keep-Alive',
                'User-Agent',
                'Cache-Control',
                'Content-Type',
                'Authorization',
                'X-Requested-With',
                'X-CSRF-Token',
                'Accept',
                'Origin',
            ],
            'expose_headers' => [],
            'allow_credentials' => true,
            'max_age' => 86400, // 24小时
        ]);
    }

    /**
     * 获取允许的来源.
     * @param string $origin 请求来源
     * @param array $config CORS 配置
     * @return null|string 允许的来源或 null
     */
    private function getAllowedOrigin(string $origin, array $config): ?string
    {
        // 如果配置了 '*' 且允许凭证，则返回实际来源
        if (in_array('*', $config['allow_origins'])) {
            if ($config['allow_credentials']) {
                return $origin ?: '*';
            }
            return '*';
        }

        // 检查是否在允许的来源列表中
        if (in_array($origin, $config['allow_origins'])) {
            return $origin;
        }

        return null;
    }
}

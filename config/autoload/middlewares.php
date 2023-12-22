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
return [
    'http' => [
        // 跨域中间件
        App\Middleware\CorsMiddleware::class,
        // 验证器中间件(官方)
        Hyperf\Validation\Middleware\ValidationMiddleware::class,
        // 维护模式中间件
        App\Middleware\MaintenanceMiddleware::class => 100,
        // 短链中间件(顺序请在jwt验证中间件前面)
        App\Middleware\ShortChainMiddleware::class => 99,
        // jwt验证中间件
        App\Middleware\AccreditMiddleware::class,
        // 权限校验中间件
        App\Middleware\AuthMiddleware::class,
    ],
];

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
use App\Middleware\AccreditMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\MaintenanceMiddleware;
use App\Middleware\ShortChainMiddleware;
use App\Middleware\WebSocketAuthMiddleware;
use Hyperf\Validation\Middleware\ValidationMiddleware;

/*
 * This file is part of Hyperf.
 *
 * @see     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'http' => [
        // 跨域中间件
        CorsMiddleware::class,
        // 验证器中间件(官方)
        ValidationMiddleware::class,
        // Session中间件(官方)
        //        Hyperf\Session\Middleware\SessionMiddleware::class,
        // 维护模式中间件
        MaintenanceMiddleware::class => 100,
        // 短链中间件(顺序请在jwt验证中间件前面)
        ShortChainMiddleware::class => 99,
        // jwt验证中间件
        AccreditMiddleware::class,
        // 权限校验中间件
        AuthMiddleware::class,
    ],
    // websocket 中间件
    'ws' => [
        // 验证中间件
        WebSocketAuthMiddleware::class,
    ],
];

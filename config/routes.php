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

use Hyperf\HttpServer\Router\Router;

// WebSocket 暂时不支持注解。
Router::addServer('ws', function () {
    // 可以新增多个WebSocket路由，对应不同的业务逻辑，只要控制器实现了对应的回调即可
    // 这里只做示例
    Router::get('/wss/demo', App\Controller\WebSocket\WebSocketController::class);
});

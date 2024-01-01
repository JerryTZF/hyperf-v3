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
    Router::get('/s', App\Controller\WebSocket\WebSocketController::class);
});

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

namespace App\Controller\WebSocket;

use Hyperf\WebSocketServer\Context;

class AbstractWebSocketController
{
    protected array $jwt;

    public function authorization($server, $request): bool
    {
        $jwt = Context::get('jwt', false);
        if (! $jwt) {
            return false;
        }

        $this->jwt = $jwt;
        return true;
    }
}

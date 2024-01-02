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

use App\Lib\Log\Log;
use App\Model\Users;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\WebSocketServer\Constant\Opcode;

class WebSocketController extends AbstractWebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage($server, $frame): void
    {
        if ($frame->opcode === Opcode::PING) {
            // 如果使用协程 Server，在判断是 PING 帧后，需要手动处理，返回 PONG 帧。
            // 异步风格 Server，可以直接通过 Swoole 配置处理，详情请见 https://wiki.swoole.com/#/websocket_server?id=open_websocket_ping_frame
            $server->push('', Opcode::PONG);
            return;
        }
        // 模拟关闭连接
        if ($frame->data === 'nihao') {
            $jwt = $this->jwt;
            $userName = Users::query()->where(['id' => $jwt['data']['uid'] ?? 0])->value('account');
            $reason = $userName . ' 已由服务端断开连接';
            $server->disconnect($frame->fd, SWOOLE_WEBSOCKET_CLOSE_NORMAL, $reason);
            return;
        }
        $server->push($frame->fd, 'Recv: ' . $frame->data);
        $server->push($frame->fd, json_encode($this->jwt));
    }

    public function onOpen($server, $request): void
    {
        // 判断鉴权中间件写入上下文的 JWT 信息, 没有 $isOk = false;
        $isOk = $this->authorization($server, $request);
        if ($isOk) {
            $server->push($request->fd, 'Authorization Success');
            Log::stdout()->info($request->fd);
        } else {
            // 外部ws中间件已经尝试解析jwt了, 失败不会连接成功, 这里再加一层判断而已, 不加也OK.
            $server->disconnect($request->fd, SWOOLE_WEBSOCKET_CLOSE_NORMAL, '非法请求');
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $jwt = $this->jwt;
        $userName = Users::query()->where(['id' => $jwt['data']['uid'] ?? 0])->value('account');
        $reason = $userName . ' 已由服务端断开连接';
        Log::stdout()->info($reason);
    }
}

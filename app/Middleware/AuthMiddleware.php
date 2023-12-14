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
use App\Service\RoleService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware extends AbstractMiddleware
{
    #[Inject]
    protected RoleService $service;

    /**
     * 权限验证.
     * @param ServerRequestInterface $request 请求类
     * @param RequestHandlerInterface $handler 处理器
     * @return ResponseInterface 响应
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 登录相关不校验
        if ($this->request->is('login/*')) {
            return $handler->handle($request);
        }

        $payload = $this->request->getAttribute('jwt');
        $uid = $payload['data']['uid'];
        $rid = $payload['data']['rid'];
        $auths = $this->service->getAuthsByRoleIds($rid);
        [$authInfos, $nodeInfos] = [$auths['auth_list'], $auths['node_list']];

        $routes = array_column($authInfos, 'route');
        if (! in_array($this->request->getPathInfo(), $routes)) {
            return $this->buildErrorResponse(ErrorCode::NO_AUTH);
        }

        return $handler->handle($request);
    }
}

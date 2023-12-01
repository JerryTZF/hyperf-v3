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

namespace App\Controller;

use App\Lib\Result\Result;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    protected string $uploadPath;

    protected array $jwtPayload;

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected Result $result;

    public function __construct()
    {
        $path = BASE_PATH . '/runtime/upload/';
        if (! is_dir($path)) {
            mkdir(iconv('GBK', 'UTF-8', $path), 0755);
        }

        $this->uploadPath = $path;
        $this->jwtPayload = $this->request->getAttribute('jwt', []);
    }

    /**
     * 获取请求IP.
     */
    public function getRequestIp(): mixed
    {
        $serverParams = $this->request->getServerParams();
        if (isset($serverParams['http_client_ip'])) {
            return $serverParams['http_client_ip'];
        }
        if (isset($serverParams['http_x_real_ip'])) {
            return $serverParams['http_x_real_ip'];
        }
        if (isset($serverParams['http_x_forwarded_for'])) {
            return Str::before($serverParams['http_x_forwarded_for'], ',');
        }

        return $serverParams['remote_addr'] ?? '';
    }
}

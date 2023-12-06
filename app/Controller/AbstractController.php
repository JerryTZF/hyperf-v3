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

/**
 * 控制器抽象类.
 * Class AbstractController.
 */
abstract class AbstractController
{
    /**
     * 上传文件临时路径.
     * @var string './runtime/upload/'
     */
    protected string $uploadPath;

    /**
     * jwt的载荷.
     * @var array|mixed ['iss' => '', 'sub' => '', 'aud' => '', ...]
     */
    protected array $jwtPayload;

    /**
     * 容器实例.
     * @var ContainerInterface 容器接口类
     */
    #[Inject]
    protected ContainerInterface $container;

    /**
     * 请求实例.
     * @var RequestInterface 请求接口类
     */
    #[Inject]
    protected RequestInterface $request;

    /**
     * 响应实例.
     * @var responseInterface 响应接口类
     */
    #[Inject]
    protected ResponseInterface $response;

    /**
     * 结果集.
     * @var Result 结果集实体类
     */
    #[Inject]
    protected Result $result;

    /**
     * 构造函数.
     */
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
     * @return mixed 'XXX.XXX.XXX.XXX'
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

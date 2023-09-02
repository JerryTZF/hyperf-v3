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
namespace App\Exception\Handler;

use App\Constants\SystemCode;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Filesystem\Exception\InvalidArgumentException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use League\Flysystem\FilesystemException;
use OSS\Core\OssException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class FileSystemExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200)->withBody(new SwooleStream(json_encode([
                'code' => SystemCode::FILE_SYSTEM_ERR,
                'msg' => SystemCode::getMessage(SystemCode::FILE_SYSTEM_ERR, [$throwable->getMessage()]),
                'status' => false,
                'data' => [],
            ], JSON_UNESCAPED_UNICODE)));
    }

    // 不同的适配器都有自己的对应的异常类, 请根据你的需求判断
    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof InvalidArgumentException
        || $throwable instanceof FilesystemException || $throwable instanceof OssException;
    }
}

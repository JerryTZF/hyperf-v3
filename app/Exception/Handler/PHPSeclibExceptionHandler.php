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
use Hyperf\HttpMessage\Stream\SwooleStream;
use phpseclib3\Exception\BadConfigurationException;
use phpseclib3\Exception\BadDecryptionException;
use phpseclib3\Exception\BadModeException;
use phpseclib3\Exception\ConnectionClosedException;
use phpseclib3\Exception\FileNotFoundException;
use phpseclib3\Exception\InconsistentSetupException;
use phpseclib3\Exception\InsufficientSetupException;
use phpseclib3\Exception\NoSupportedAlgorithmsException;
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Exception\UnsupportedAlgorithmException;
use phpseclib3\Exception\UnsupportedCurveException;
use phpseclib3\Exception\UnsupportedOperationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class PHPSeclibExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        // 禁止异常冒泡
        $this->stopPropagation();

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200)->withBody(new SwooleStream(json_encode([
                'code' => SystemCode::PHPSECLIB_ERR,
                'msg' => SystemCode::getMessage(SystemCode::PHPSECLIB_ERR, [$throwable->getMessage()]),
                'status' => false,
                'data' => [],
            ], JSON_UNESCAPED_UNICODE)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return match ($throwable) {
            $throwable instanceof BadConfigurationException => true,
            $throwable instanceof BadDecryptionException => true,
            $throwable instanceof BadModeException => true,
            $throwable instanceof ConnectionClosedException => true,
            $throwable instanceof FileNotFoundException => true,
            $throwable instanceof InconsistentSetupException => true,
            $throwable instanceof InsufficientSetupException => true,
            $throwable instanceof NoSupportedAlgorithmsException => true,
            $throwable instanceof UnableToConnectException => true,
            $throwable instanceof UnsupportedAlgorithmException => true,
            $throwable instanceof UnsupportedCurveException => true,
            $throwable instanceof UnsupportedOperationException => true,
            default => false,
        };
    }
}

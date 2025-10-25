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
use App\Exception\Handler\AlibabaExceptionHandler;
use App\Exception\Handler\AppExceptionHandler;
use App\Exception\Handler\BusinessExceptionHandler;
use App\Exception\Handler\FileSystemExceptionHandler;
use App\Exception\Handler\JwtExceptionHandler;
use App\Exception\Handler\LockTimeoutExceptionHandler;
use App\Exception\Handler\ModelNotFoundExceptionHandler;
use App\Exception\Handler\OfficeExceptionHandler;
use App\Exception\Handler\PHPSeclibExceptionHandler;
use App\Exception\Handler\RateLimitExceptionHandler;
use App\Exception\Handler\ValidationExceptionHandler;
use App\Exception\Handler\WebsocketExceptionHandler;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;

/**
 * This file is part of Hyperf.
 *
 * @see     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'handler' => [
        'http' => [
            // JWT认证失败
            JwtExceptionHandler::class,
            // 业务逻辑异常
            BusinessExceptionHandler::class,
            // 验证器类型错误处理
            ValidationExceptionHandler::class,
            // 文件系统异常捕获
            FileSystemExceptionHandler::class,
            // 数据库未找到数据异常处理
            ModelNotFoundExceptionHandler::class,
            // 限流异常处理器
            RateLimitExceptionHandler::class,
            // redis锁组件异常处理器
            LockTimeoutExceptionHandler::class,
            // 阿里云包底层异常捕获器
            AlibabaExceptionHandler::class,
            // phpoffice 包异常捕获
            OfficeExceptionHandler::class,
            // PHPSeclib 包异常捕获
            PHPSeclibExceptionHandler::class,
            // 全局(框架)异常处理
            AppExceptionHandler::class,
            // 全局HTTP异常处理
            HttpExceptionHandler::class,
        ],
        // websocket exception handler
        'ws' => [
            WebsocketExceptionHandler::class,
        ],
    ],
];

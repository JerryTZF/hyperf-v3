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

namespace App\Lib\Log;

use App\Model\LogRecords;
use Carbon\Carbon;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * @method static void info(string $msg, array $content = [], string $straceString = '')
 * @method static void warning(string $msg, array $content = [], string $straceString = '')
 * @method static void error(string $msg, array $content = [], string $straceString = '')
 * @method static void alert(string $msg, array $content = [], string $straceString = '')
 * @method static void critical(string $msg, array $content = [], string $straceString = '')
 * @method static void emergency(string $msg, array $content = [], string $straceString = '')
 * @method static void notice(string $msg, array $content = [], string $straceString = '')
 */
class Log
{
    /**
     * 静态调用.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function __callStatic(string $level, array $args = []): void
    {
        // 获取最近两层的调用栈信息
        [$headTrace, $lastTrace] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        // 静态调用参数处理
        [$message, $context, $traceString] = [
            $args[0] ?? '',
            $args[1] ?? [],
            $args[2] ?? '',
        ];
        // 节点channel
        $channel = "{$lastTrace['class']}@{$lastTrace['function']}";
        // 当前日期
        $nowDate = Carbon::now()->toDateTimeString();
        // string context
        $contextString = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 写入数据库(可以用其他方式替代日志上报)
        (new LogRecords([
            'level' => Str::upper($level),
            'class' => $lastTrace['class'],
            'function' => $lastTrace['function'],
            'message' => $message,
            'context' => $contextString,
            'file' => $headTrace['file'],
            'line' => $headTrace['line'],
            'trace' => $traceString,
        ]))->save();

        // CLI输出(没有$content)
        static::stdout()->{$level}("[{$nowDate}][{$channel}][{$message}][{$contextString}]");
        // DISK输出
        static::get($channel)->{$level}($message, $args[1] ?? []);
    }

    /**
     * 获取Logger实例.
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     */
    public static function get(string $channel = ''): LoggerInterface
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($channel);
    }

    /**
     * CLI 日志实例.
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     */
    public static function stdout(): StdoutLoggerInterface
    {
        return ApplicationContext::getContainer()->get(StdoutLoggerInterface::class);
    }
}

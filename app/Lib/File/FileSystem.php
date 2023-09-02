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
namespace App\Lib\File;

use Hyperf\Context\ApplicationContext;
use Hyperf\Filesystem\FilesystemFactory;
use League\Flysystem\FilesystemException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

// 底层API请参考: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
class FileSystem
{
    /**
     * 文件系统实例.
     */
    private \League\Flysystem\Filesystem $fileInstance;

    /**
     * 适配器请根据对应的配置进行填写.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(string $adapterName = 'oss')
    {
        $factory = ApplicationContext::getContainer()->get(FilesystemFactory::class);
        $this->fileInstance = $factory->get($adapterName);
    }

    // -------------------------------------------------------
    // | 读取相关
    // -------------------------------------------------------

    /**
     * 读取文件, 输出文件内容.
     * @throws FilesystemException
     */
    public function read(string $filename, bool $withStream = false): mixed
    {
        return $withStream ? $this->fileInstance->readStream($filename) :
            $this->fileInstance->read($filename);
    }

    // -------------------------------------------------------
    // | 写入相关
    // -------------------------------------------------------

    /**
     * 写入文件.
     * @throws FilesystemException
     */
    public function write(string $path, string $content, bool $isCover = true): void
    {
        if ($isCover) {
            $this->fileInstance->write($path, $content);
        }
    }

    public function test(): array
    {
        $file = '/img/20210430171345.png';
        $a = $this->fileInstance->has($file);
        $b = $this->fileInstance->fileSize($file);
        var_dump($a, $b);
        return [];
    }
}

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
use JetBrains\PhpStorm\ArrayShape;
use League\Flysystem\FilesystemException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

// 底层API请参考: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
// 异常已经在框架注册, 无需处理
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

    /**
     * 读取文件, 输出文件内容.
     * @return resource|string
     * @throws FilesystemException
     */
    public function read(string $filename, bool $withStream = false)
    {
        return $withStream ? $this->fileInstance->readStream($filename) :
            $this->fileInstance->read($filename);
    }

    /**
     * 写入文件.
     * @throws FilesystemException
     */
    public function write(string $filename, mixed $content, bool $withStream = false, bool $isCover = true): void
    {
        $isHas = $this->fileInstance->has($filename);
        if ($isCover || (! $isHas)) {
            $withStream ? $this->fileInstance->writeStream($filename, $content) :
                $this->fileInstance->write($filename, $content);
        }
    }

    /**
     * 删除文件.
     * @throws FilesystemException
     */
    public function delete(string $path): void
    {
        $this->fileInstance->delete($path);
    }

    /**
     * 获取目录下文件列表.
     * @param bool $recursive 是否递归查询
     * @throws FilesystemException
     */
    public function list(string $path, bool $recursive = false): array
    {
        return $this->fileInstance->listContents($path, $recursive)->toArray();
    }

    /**
     * 获取文件的元数据.
     * @throws FilesystemException
     */
    #[ArrayShape(['visibility' => 'mixed', 'size' => 'mixed', 'mime' => 'mixed', 'last_modified' => 'int'])]
    public function getFileMetaData(string $filename): array
    {
        return [
            'visibility' => $this->fileInstance->visibility($filename),
            'size' => $this->fileInstance->fileSize($filename),
            'mime' => $this->fileInstance->mimeType($filename),
            'last_modified' => $this->fileInstance->lastModified($filename),
        ];
    }

    /**
     * 获取文件系统实例.
     */
    public function getInstance(): \League\Flysystem\Filesystem
    {
        return $this->fileInstance;
    }
}

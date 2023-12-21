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

use App\Constants\SystemCode;
use App\Exception\BusinessException;
use ZipArchive;

class Zip
{
    /**
     * 压缩多个文件到zip.
     * @param string $zipName zip文件路径
     * @param array $fileList ['path/file1.txt', 'path/file2.txt']
     */
    public static function compress(string $zipName, array $fileList)
    {
        $zip = new ZipArchive();
        $isOpenOk = $zip->open($zipName, ZipArchive::CREATE);
        if ($isOpenOk !== true) {
            throw new BusinessException(SystemCode::ZIP_ERR, SystemCode::getMessage(SystemCode::ZIP_ERR));
        }
        $fileList = array_unique($fileList);
        foreach ($fileList as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    }
}

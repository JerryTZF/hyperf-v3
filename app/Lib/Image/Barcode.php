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
namespace App\Lib\Image;

use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;

class Barcode
{
    /**
     * 生成器.
     */
    private BarcodeGenerator|BarcodeGeneratorPNG $generator;

    /**
     * 条码类型.
     * @see https://github.com/picqer/php-barcode-generator#accepted-barcode-types
     * @var mixed|string
     */
    private string $barType;

    /**
     * 条码宽度(单根竖条宽度).
     * @var int|mixed
     */
    private int $width;

    /**
     * 条码高度.
     * @var int|mixed
     */
    private int $height;

    /**
     * 前景色.
     * @var int[]|mixed
     */
    private array $foregroundColor;

    /**
     * 保存到本地路径.
     * @var mixed|string
     */
    private string $path;

    public function __construct(array $config = [])
    {
        $this->generator = new BarcodeGeneratorPNG();
        $this->barType = $config['bar_type'] ?? $this->generator::TYPE_CODE_128;
        $this->width = $config['width'] ?? 1;
        $this->height = $config['height'] ?? 50;
        $this->foregroundColor = $config['foreground_color'] ?? [0, 0, 0];
        $this->path = $config['path'] ?? BASE_PATH . '/runtime/barcode/';

        if (! is_dir($this->path)) {
            mkdir(iconv('GBK', 'UTF-8', $this->path), 0755);
        }
    }

    /**
     * 获取条形码字符串.
     */
    public function getStream(string $content = ''): string
    {
        return $this->generator->getBarcode($content, $this->barType, $this->width, $this->height, $this->foregroundColor);
    }

    /**
     * 保存条码到本地.
     */
    public function move(string $filename, string $content = ''): void
    {
        file_put_contents(
            $this->path . $filename,
            $this->generator->getBarcode($content, $this->barType, $this->width, $this->height, $this->foregroundColor)
        );
    }
}

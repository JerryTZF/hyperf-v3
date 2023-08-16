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

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Exception;

class Qrcode
{
    /**
     * 文字编码
     */
    private string $encoding = 'UTF-8';

    /**
     * 二维码尺寸.
     * @var int|mixed
     */
    private int $size;

    /**
     * 边距.
     * @var int|mixed
     */
    private int $margin;

    /**
     * logo路径.
     * @var mixed|string
     */
    private string $logoPath;

    /**
     * label文字.
     * @var mixed|string
     */
    private string $labelText;

    /**
     * 二维码保存路径.
     * @var mixed|string
     */
    private string $path;

    /**
     * 前景色.
     * @var int[]|mixed
     */
    private array $foregroundColor;

    /**
     * 背景色.
     * @var int[]|mixed
     */
    private array $backgroundColor;

    public function __construct(array $config = [])
    {
        $this->size = $config['size'] ?? 300;
        $this->margin = $config['margin'] ?? 10;
        $this->logoPath = $config['logo_path'] ?? '';
        $this->labelText = $config['label_text'] ?? '';
        $this->path = $config['path'] ?? BASE_PATH . '/runtime/qrcode/';
        $this->foregroundColor = $config['foreground_color'] ?? [0, 0, 0];
        $this->backgroundColor = $config['background_color'] ?? [255, 255, 255];

        if (! is_dir($this->path)) {
            mkdir(iconv('GBK', 'UTF-8', $this->path), 0755);
        }
    }

    /**
     * 获取二维码字符串.
     */
    public function getStream(string $content): string
    {
        return $this->getResult($content)->getString();
    }

    /**
     * 保存二维码到本地.
     */
    public function move(string $filename, string $content): void
    {
        $this->getResult($content)->saveToFile($this->path . $filename);
    }

    /**
     * 制作二维码
     * @throws Exception
     */
    private function getResult(string $content): ResultInterface
    {
        $writer = new PngWriter();
        $qrCode = \Endroid\QrCode\QrCode::create($content)
            ->setEncoding(new Encoding($this->encoding))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize($this->size)
            ->setMargin($this->margin)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(...$this->foregroundColor))
            ->setBackgroundColor(new Color(...$this->backgroundColor));

        $logo = ! empty($this->logoPath) ? Logo::create($this->logoPath)
            ->setResizeToWidth(50)
            ->setPunchoutBackground(true) : null;

        $label = ! empty($this->labelText) ? Label::create($this->labelText)
            ->setTextColor(new Color(255, 0, 0)) : null;

        return $writer->write($qrCode, $logo, $label);
    }
}

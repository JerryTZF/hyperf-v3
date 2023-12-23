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

namespace App\Lib\Office;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response;
use Psr\Http\Message\ResponseInterface;

class ExportCsvHandler
{
    /**
     * csv字符串.
     * @var string 内容字符串
     */
    private string $content = '';

    /**
     * 保存路径.
     * @var string 路径
     */
    private string $dir;

    public function __construct()
    {
        $this->dir = BASE_PATH . '/runtime/csv/';
        if (! is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    /**
     * 设置表头.
     * @example ['汽车品牌', '型号', '颜色', '价格', '经销商']
     * @return $this
     */
    public function setHeaders(array $headers): static
    {
        $this->content .= mb_convert_encoding(implode(',', $headers), 'GBK', 'UTF-8') . "\n";
        return $this;
    }

    /**
     * 添加数据.
     * @return $this
     */
    public function setData(array $data): static
    {
        if (! empty($this->content)) {
            foreach ($data as $value) {
                $this->content .= mb_convert_encoding(implode(',', array_values($value)), 'GBK', 'UTF-8') . "\n";
            }
        }
        return $this;
    }

    /**
     * 保存CSV到本地.
     * @param string $filename 文件名
     * @return string 文件
     */
    public function saveToLocal(string $filename): string
    {
        $filename .= '.csv';
        $filename = mb_convert_encoding($filename, 'UTF-8', 'auto');
        $outFileName = $this->dir . $filename;
        file_put_contents($outFileName, $this->content);

        return $outFileName;
    }

    /**
     * 输出到浏览器.
     * @param string $filename 文件名
     * @return ResponseInterface 文件流
     */
    public function saveToBrowser(string $filename): ResponseInterface
    {
        $filename .= '.csv';
        $filename = mb_convert_encoding($filename, 'UTF-8', 'auto');
        $response = new Response();
        return $response->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'text/csv')
            ->withHeader('content-disposition', 'attachment; filename="' . urlencode($filename) . '"')
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withBody(new SwooleStream($this->content));
    }
}

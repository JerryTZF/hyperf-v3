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
     */
    private string $content = '';

    /**
     * 保存路径.
     */
    private string $dir;

    public function __construct()
    {
        $this->dir = BASE_PATH . '/runtime/csv/';
        if (! is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
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
     */
    public function saveToLocal(string $filename): string
    {
        $filename .= '.csv';
        $outFileName = $this->dir . $filename;
        file_put_contents($outFileName, $this->content);

        return $outFileName;
    }

    /**
     * 输出到浏览器.
     */
    public function saveToBrowser(string $filename): ResponseInterface
    {
        $filename .= '.csv';
        $response = new Response();
        return $response->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'text/csv')
            ->withHeader('content-disposition', "attachment; filename={$filename}")
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withBody(new SwooleStream($this->content));
    }
}

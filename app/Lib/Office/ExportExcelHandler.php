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
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Psr\Http\Message\ResponseInterface;

class ExportExcelHandler
{
    /**
     * 表格制作实例.
     */
    private Spreadsheet $spreadsheet;

    /**
     * 表格实例.
     */
    private Worksheet $sheet;

    /**
     * 行数.
     */
    private int $row;

    /**
     * 保存路径.
     */
    private string $dir;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()
            ->setCreator('Hyperf')
            ->setLastModifiedBy('Hyperf')
            ->setTitle('ExportExcelHandler 2007 XLSX Test Document')
            ->setSubject('ExportExcelHandler 2007 XLSX Test Document')
            ->setDescription('Test document for ExportExcelHandler 2007 XLSX, generated using PHP classes.')
            ->setKeywords('')
            ->setCategory('Excel');
        // Sets the active sheet index to the first sheet
        $this->spreadsheet->setActiveSheetIndex(0);
        // Active sheet
        $this->sheet = $this->spreadsheet->getActiveSheet();
        // Set name
        $this->spreadsheet->getActiveSheet()->setTitle('Sheet1');

        $this->dir = BASE_PATH . '/runtime/excel/';
        if (! is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    /**
     * 设置表头.
     * @return $this
     * @example ['汽车品牌', '型号', '颜色', '价格', '经销商']
     */
    public function setHeaders(array $title): static
    {
        foreach ($title as $k => $item) {
            $this->sheet->setCellValue(chr($k + 65) . '1', $item);
        }
        $this->row = 2;
        return $this;
    }

    /**
     * 添加数据.
     * @return $this
     * @example [['宝马','X5','BLACK','54.12W','深圳宝马4S店'],[],[]]
     */
    public function setData(array $data): static
    {
        foreach ($data as $datum) {
            $col = 'A';
            foreach ($datum as $value) {
                // write col
                $this->sheet->setCellValue($col . $this->row, $value);
                ++$col;
            }
            ++$this->row;
        }
        return $this;
    }

    /**
     * 保存到本地.
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function saveToLocal(string $filename): string
    {
        $this->spreadsheet->setActiveSheetIndex(0);

        $filename .= '.xlsx';
        $outFileName = $this->dir . $filename;
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save($outFileName);

        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        return $outFileName;
    }

    /**
     * 输出到浏览器.
     * @throws Exception
     */
    public function saveToBrowser(string $filename): ResponseInterface
    {
        $filename .= '.xlsx';
        $unique = $this->dir . uniqid() . microtime() . '.xlsx';
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save($unique);

        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        $content = file_get_contents($unique);
        // 删除临时文件
        unlink($unique);

        $response = new Response();

        return $response->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('content-disposition', "attachment; filename={$filename}")
            ->withHeader('content-transfer-encoding', 'binary')
            ->withBody(new SwooleStream((string) $content));
    }
}

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

namespace App\Controller;

use App\Lib\Lock\RedisLock;
use App\Lib\Office\ExportCsvHandler;
use App\Lib\Office\ExportExcelHandler;
use App\Model\Orders;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Lysice\HyperfRedisLock\LockTimeoutException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: 'office')]
class OfficeController extends AbstractController
{
    /**
     * 导出订单Excel(同步).
     * @return MessageInterface|ResponseInterface 文件流
     * @throws ContainerExceptionInterface 异常
     * @throws LockTimeoutException 异常
     * @throws NotFoundExceptionInterface 异常
     */
    #[GetMapping(path: 'export/excel')]
    public function exportOrderExcel(): MessageInterface|ResponseInterface
    {
        // 使用锁防止并发导出消耗大量内存.
        $lock = new RedisLock('exportExcel', 5, 3, 'exportOrderExcel');
        return $lock->lockAsync(function () {
            $excelHandler = new ExportExcelHandler();
            // 设置表头
            $excelHandler->setHeaders([
                '序号', '商品ID', '商品名称', '订单编号', '购买数量', '支付金额', '买家昵称', '创建订单时间',
            ]);
            // 分块设置数据
            $fields = [
                'orders.id', 'orders.gid', 'goods.name', 'orders.order_no', 'orders.number', 'orders.payment_money',
                'users.account', 'orders.create_time',
            ];
            Orders::query()
                ->leftJoin('users', 'users.id', '=', 'orders.uid')
                ->leftJoin('goods', 'goods.id', '=', 'orders.gid')
                ->where('orders.number', '>', 1)
                ->select($fields)
                ->chunk(20, function ($records) use ($excelHandler) {
                    $excelHandler->setData($records->toArray());
                });
            return $excelHandler->saveToBrowser('订单列表导出');
        });
    }

    /**
     * 导出订单Csv(同步).
     * @return MessageInterface|ResponseInterface 文件流
     * @throws ContainerExceptionInterface 异常
     * @throws LockTimeoutException 异常
     * @throws NotFoundExceptionInterface 异常
     */
    #[GetMapping(path: 'export/csv')]
    public function exportOrderCsv(): MessageInterface|ResponseInterface
    {
        // 使用锁防止并发导出消耗大量内存.
        $lock = new RedisLock('exportCsv', 5, 3, 'exportOrderCsv');
        return $lock->lockAsync(function () {
            $csvHandler = new ExportCsvHandler();
            // 设置表头
            $csvHandler->setHeaders([
                '序号', '商品ID', '商品名称', '订单编号', '购买数量', '支付金额', '买家昵称', '创建订单时间',
            ]);
            // 分块设置数据
            $fields = [
                'orders.id', 'orders.gid', 'goods.name', 'orders.order_no', 'orders.number', 'orders.payment_money',
                'users.account', 'orders.create_time',
            ];
            Orders::query()
                ->leftJoin('users', 'users.id', '=', 'orders.uid')
                ->leftJoin('goods', 'goods.id', '=', 'orders.gid')
                ->where('orders.number', '>', 1)
                ->select($fields)
                ->chunk(20, function ($records) use ($csvHandler) {
                    $csvHandler->setData($records->toArray());
                });
            return $csvHandler->saveToBrowser('订单列表导出');
        });
    }
}

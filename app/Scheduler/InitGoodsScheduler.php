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

namespace App\Scheduler;

use App\Model\Goods;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(
    rule: '*\/5 * * * *',
    name: 'InitGoodsScheduler',
    onOneServer: true,
    callback: 'execute',
    memo: '每5分钟初始化商品(商品库存为零时自动增加100库存)',
    enable: 'isEnable'
)]
class InitGoodsScheduler
{
    public function execute(): void
    {
        $goods = Goods::query()->limit(10)->get();
        if (empty($goods->toArray())) {
            $this->initGoods();
        } else {
            /** @var Goods $good */
            foreach ($goods as $good) {
                if ($good->stock <= 0) {
                    $good->stock = 100;
                    $good->save();
                }
            }
        }
    }

    /**
     * 是否随着服务启动开始该任务.
     * @return bool 是否执行该定时任务
     */
    public function isEnable(): bool
    {
        return true;
    }

    private function initGoods(): void
    {
        $nowDate = Carbon::now()->toDateTimeString();
        $initGoods = [
            [
                'name' => 'IPhone15 Pro 1TB 暗紫色',
                'price' => 5999.00,
                'stock' => 100,
                'brand' => 'Apple',
                'create_time' => $nowDate,
                'update_time' => $nowDate,
            ],
            [
                'name' => 'Xiaomi 14 128G 雪花白',
                'price' => 4888.88,
                'stock' => 100,
                'brand' => '小米',
                'create_time' => $nowDate,
                'update_time' => $nowDate,
            ],
            [
                'name' => 'Huawei p50 pro 256G 标准版',
                'price' => 5666.00,
                'stock' => 100,
                'brand' => '华为',
                'create_time' => $nowDate,
                'update_time' => $nowDate,
            ],
            [
                'name' => '赣州脐橙 一箱 5KG',
                'price' => 48.99,
                'stock' => 100,
                'brand' => '赣州脐橙',
                'create_time' => $nowDate,
                'update_time' => $nowDate,
            ],
            [
                'name' => '腾讯音乐VIP会员 1个月',
                'price' => 29.99,
                'stock' => 100,
                'brand' => '腾讯',
                'create_time' => $nowDate,
                'update_time' => $nowDate,
            ],
        ];

        Goods::insert($initGoods);
    }
}

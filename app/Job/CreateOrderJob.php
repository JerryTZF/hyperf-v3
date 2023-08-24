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
namespace App\Job;

use App\Lib\Log\Log;
use App\Model\Goods;
use App\Model\Orders;
use Hyperf\Stringable\Str;

class CreateOrderJob extends AbstractJob
{
    public function __construct(string $uniqueId, array $params)
    {
        parent::__construct($uniqueId, $params);
    }

    public function handle()
    {
        [$gid, $num] = [$this->params['gid'], $this->params['num']];
        /** @var Goods $goodInfo */
        $goodInfo = Goods::where(['id' => $gid])->firstOrFail();
        if ($goodInfo->stock > 0 && $goodInfo->stock >= $num) {
            (new Orders([
                'gid' => $goodInfo->id,
                'order_id' => Str::random() . uniqid(),
                'number' => $num,
                'money' => $goodInfo->price * $num,
                'customer' => 'Jerry',
            ]))->save();
            $goodInfo->stock = $goodInfo->stock - $num;
            $goodInfo->save();
        } else {
            Log::warning("{$goodInfo->name} 库存不足 !!!");
        }
    }
}

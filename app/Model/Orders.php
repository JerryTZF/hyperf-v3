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

namespace App\Model;

/**
 * Class Orders.
 * @property int $id
 * @property int $gid
 * @property int $uid
 * @property string $order_no
 * @property int $number
 * @property float $payment_money
 * @property string $create_time
 * @property string $update_time
 */
class Orders extends Model
{
    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    /**
     * 表名称.
     */
    protected ?string $table = 'orders';

    /**
     * 允许被批量赋值的字段集合(黑名单).
     */
    protected array $guarded = [];

    /**
     * 数据格式化配置.
     */
    protected array $casts = [
        'id' => 'integer',
        'gid' => 'integer',
        'uid' => 'integer',
        'create_time' => 'Y-m-d H:i:s',
        'update_time' => 'Y-m-d H:i:s',
        'payment_money' => 'decimal:2',
    ];

    /**
     * 时间格式.
     */
    protected ?string $dateFormat = 'Y-m-d H:i:s';
}

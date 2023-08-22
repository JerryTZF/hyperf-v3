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
 * Class Goods.
 * @property int $id
 * @property string $name
 * @property float $price
 * @property int $stock
 * @property string $brand
 * @property int $version
 * @property string $create_time
 * @property string $update_time
 */
class Goods extends Model
{
    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    /**
     * 表名称.
     */
    protected ?string $table = 'goods';

    /**
     * 允许被批量赋值的字段集合(黑名单).
     */
    protected array $guarded = [];

    /**
     * 数据格式化配置.
     */
    protected array $casts = [
        'id' => 'integer',
        'version' => 'integer',
        'create_time' => 'Y-m-d H:i:s',
        'update_time' => 'Y-m-d H:i:s',
        'price' => 'decimal:2',
    ];

    /**
     * 时间格式.
     */
    protected ?string $dateFormat = 'Y-m-d H:i:s';
}

<?php

namespace App\Model;

class Goods extends Model
{
    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    /**
     * 表名称
     * @var ?string
     */
    protected ?string $table = 'goods';

    /**
     * 允许被批量赋值的字段集合
     * @var array
     */
    protected array $guarded = [];

    /**
     * 数据格式化配置
     * @var array
     */
    protected array $casts = [
        'id'          => 'integer',
        'create_time' => 'Y-m-d H:i:s',
        'update_time' => 'Y-m-d H:i:s'
    ];

}
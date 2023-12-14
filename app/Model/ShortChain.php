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
 * @property int $id
 * @property int $uid
 * @property string $url
 * @property string $short_chain
 * @property string $hash_code
 * @property string $expire_at
 * @property string $status
 * @property string $create_time
 * @property string $update_time
 */
class ShortChain extends Model
{
    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public const STATUS_ARR = ['active', 'ban'];

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BAN = 'ban';

    protected string $primaryKey = 'id';

    protected ?string $connection = 'default';

    /**
     * 表名称.
     */
    protected ?string $table = 'short_chain';

    /**
     * 允许被批量赋值的字段集合(黑名单).
     */
    protected array $guarded = [];

    /**
     * 数据格式化配置.
     */
    protected array $casts = [
        'id' => 'integer',
        'uid' => 'integer',
        'create_time' => 'Y-m-d H:i:s',
        'update_time' => 'Y-m-d H:i:s',
    ];

    /**
     * 时间格式.
     */
    protected ?string $dateFormat = 'Y-m-d H:i:s';
}

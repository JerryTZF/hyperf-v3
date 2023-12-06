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
 * @property string $route
 * @property string $status
 * @property string $create_time
 * @property string $update_time
 */
class Auths extends Model
{
    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BAN = 'ban';

    public const STATUS_PAUSE = 'pause';

    protected string $primaryKey = 'id';

    protected array $status = ['active', 'ban', 'pause'];

    protected ?string $connection = 'default';

    /**
     * 表名称.
     */
    protected ?string $table = 'auths';

    /**
     * 允许被批量赋值的字段集合(黑名单).
     */
    protected array $guarded = [];

    /**
     * 数据格式化配置.
     */
    protected array $casts = [
        'id' => 'integer',
        'create_time' => 'Y-m-d H:i:s',
        'update_time' => 'Y-m-d H:i:s',
    ];

    /**
     * 时间格式.
     */
    protected ?string $dateFormat = 'Y-m-d H:i:s';

    /**
     * status修改器.
     */
    public function setStatusAttribute(mixed $value): void
    {
        $this->attributes['status'] = in_array($value, $this->status) ? $value : 'active';
    }
}

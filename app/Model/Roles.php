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
 * @property string $role_name
 * @property string $auth_id
 * @property string $node_id
 * @property string $status
 * @property string $create_time
 * @property string $update_time
 *                               Class Roles
 */
class Roles extends Model
{
    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DELETE = 'delete';

    public const STATUS_PAUSE = 'pause';

    public const STATUS_ARR = ['active', 'delete', 'pause'];

    protected string $primaryKey = 'id';

    protected ?string $connection = 'default';

    /**
     * 表名称.
     */
    protected ?string $table = 'roles';

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
        $this->attributes['status'] = in_array($value, self::STATUS_ARR) ? $value : 'active';
    }

    /**
     * 权限节点ID转数组.
     * @param mixed $value 字段值
     * @return array 权限节点数组
     */
    public function getAuthIdAttribute(mixed $value): array
    {
        return $value === '' ? [] : explode(',', trim($value, ','));
    }
}

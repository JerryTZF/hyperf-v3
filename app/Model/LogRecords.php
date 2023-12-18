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
 * @property int $line
 * @property string $level
 * @property string $class
 * @property string $function
 * @property string $message
 * @property string $context
 * @property string $file
 * @property string $trace
 * @property string $create_time
 * @property string $update_time
 */
class LogRecords extends Model
{
    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    protected string $primaryKey = 'id';

    protected ?string $connection = 'default';

    protected ?string $table = 'log_records';

    protected array $guarded = [];

    protected array $casts = [
        'id' => 'integer',
        'create_time' => 'Y-m-d H:i:s',
        'update_time' => 'Y-m-d H:i:s',
    ];

    protected ?string $dateFormat = 'Y-m-d H:i:s';
}

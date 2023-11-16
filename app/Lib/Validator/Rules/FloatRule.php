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

namespace App\Lib\Validator\Rules;

use Hyperf\Validation\Validator;

class FloatRule implements RuleInterface
{
    public const NAME = 'float';

    public function passes($attribute, $value, $parameters, Validator $validator): bool
    {
        return gettype($value) === 'double';
    }

    public function message($message, $attribute, $rule, $parameters, Validator $validator): string
    {
        return '必须为保留三位的float类型';
    }
}

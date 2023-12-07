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

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class RoleRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['role_name'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_name' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_name.required' => 'role_name 角色必填',
            'role_name.string' => 'role_name 必须为字符串',
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

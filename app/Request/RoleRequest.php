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

use App\Model\Roles;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class RoleRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['role_name'],
        'bind' => ['role_id', 'auth_id'],
        'update' => ['role_id', 'status'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_name' => ['required', 'string'],
            'role_id' => ['required', 'integer'],
            'auth_id' => ['required', 'array', 'array_list'],
            'status' => [Rule::in(Roles::STATUS_ARR)],
        ];
    }

    public function messages(): array
    {
        $statusString = implode(',', Roles::STATUS_ARR);
        return [
            'role_name.required' => 'role_name 角色必填',
            'role_name.string' => 'role_name 必须为字符串',
            'role_id.required' => 'role_id 角色ID必填',
            'auth_id.required' => 'auth_id 权限节点ID必填',
            'role_id.integer' => 'role_id 角色ID必须为整型',
            'auth_id.array' => 'auth_id 权限节点ID必须为数组',
            'auth_id.array_list' => 'auth_id 权限节点ID必须为非关联数组',
            'status.in' => "角色状态只能为 {$statusString}",
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

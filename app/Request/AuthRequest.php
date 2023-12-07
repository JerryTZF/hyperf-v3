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

use App\Model\Auths;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

// 规则: https://learnku.com/docs/laravel/9.x/validation/12219#189a36
class AuthRequest extends FormRequest
{
    protected array $scenes = [
        'update' => ['auth_id', 'status'],
        'belong' => ['route']
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Auths::STATUS_ARR)],
            'auth_id' => ['required', 'integer'],
            'route' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        $statusString = implode(',', Auths::STATUS_ARR);
        return [
            'status.required' => 'status 状态必填',
            'status.in' => "status 状态只能为 {$statusString}",
            'auth_id.required' => 'auth_id 权限ID必填',
            'auth_id.integer' => 'auth_id 权限ID只能为整数',
            'route.required' => 'route 必填',
            'route.string' => 'route 必须是合法的路由路径',
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

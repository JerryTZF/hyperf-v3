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

use App\Model\Users;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class UserRequest extends FormRequest
{
    protected array $scenes = [
        'update_password' => ['password', 'password_confirmation'],
        'update_info' => ['phone', 'age', 'sex', 'status'],
        'bind' => ['role_id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'alpha_num', 'confirmed'],
            'password_confirmation' => ['required', 'same:password'],
            'phone' => ['phone'],
            'age' => ['integer', 'between:0,100'],
            'sex' => [Rule::in(['man', 'woman', 'others'])],
            'status' => [Rule::in(Users::STATUS_ARR)],
            'role_id' => ['required', 'array_list'],
        ];
    }

    public function messages(): array
    {
        $statusString = implode(',', Users::STATUS_ARR);
        return [
            'password.required' => 'password 密码必填',
            'password.alpha_num' => 'password 密码必须是字母或数字',
            'password.confirmed' => '密码不一致',
            'password_confirmation.required' => '确认密码必填',
            'phone.phone' => 'phone 手机号非法',
            'age.integer' => 'age 年龄必须必须为整数',
            'age.between' => 'age 年龄必须在0,100之间',
            'sex.in' => 'sex 性别只能为man,woman,others',
            'status.in' => "status 状态只能是 {$statusString}",
            'role_id.required' => 'role_id 角色ID必填',
            'role_id.array_list' => 'role_id 角色ID必须为数组',
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

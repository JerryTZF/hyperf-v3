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

class LoginRequest extends FormRequest
{
    protected array $scenes = [
        'get_jwt' => ['account', 'pwd'],
        'register' => ['account', 'password', 'password_confirmation', 'phone', 'code'],
        'explain_jwt' => ['jwt'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account' => ['string', 'required'],
            'pwd' => ['required', 'alpha_num'],
            'password' => ['required', 'alpha_num', 'confirmed'],
            'password_confirmation' => ['required', 'same:password'],
            'phone' => ['required', 'phone'],
            'jwt' => ['required', 'string'],
            'code' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'account.string' => 'account 账号必须为字符串',
            'account.required' => 'account 账号必填',
            'password.required' => 'password 密码必填',
            'pwd.required' => '密码必填',
            'password.alpha_num' => 'password 密码必须是字母或数字',
            'password.confirmed' => '密码不一致',
            'phone.required' => 'phone 手机号必填',
            'phone.phone' => 'phone 非法',
            'password_confirmation.required' => '确认密码必填',
            'jwt.required' => 'jwt 必填',
            'jwt.string' => 'jwt 只能是字符串',
            'code.required' => '手机验证码必填',
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

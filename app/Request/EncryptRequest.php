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
use Hyperf\Validation\Rule;

class EncryptRequest extends FormRequest
{
    protected array $scenes = [
        'aes' => ['key', 'cipher_type', 'cipher_length', 'option', 'output_type', 'data'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string'],
            'cipher_type' => ['required', Rule::in(['cbc', 'ecb', 'gcm', 'ocb'])],
            'cipher_length' => ['required', Rule::in(['128', '192', '256'])],
            'output_type' => ['required', Rule::in(['base64', 'hex'])],
            'data' => ['required'],
            'option' => ['array'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => '秘钥 key必填',
            'key.string' => '秘钥 key必须为字符串',
            'cipher_type.required' => '密码学方式 cipher_type 必填',
            'cipher_length.required' => '密码学方式长度 cipher_length 必填',
            'cipher_type.in' => "密码学方式类型 cipher_type 只能为：'cbc', 'ecb', 'gcm', 'ocb'",
            'cipher_length.in' => "密码学方式长度 cipher_length 只能为：'128', '192', '256'",
            'data.required' => 'data 待加解密数据必填',
            'output_type.required' => '转换类型必填',
            'output_type.in' => '转换类型只能是 base64 或者 hex',
            'option.array' => 'option 只能是数组',
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

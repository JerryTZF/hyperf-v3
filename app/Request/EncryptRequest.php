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
        'rsa_create' => ['key_format', 'key_length', 'is_download'],
        'encrypt_decrypt' => ['key', 'padding', 'hash', 'mgf_hash'],
        'sign' => ['key', 'padding', 'hash', 'mgf_hash'],
        'rc4' => ['key', 'data'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string'],
            'public_key' => ['required', 'string'],
            'cipher_type' => ['required', Rule::in(['cbc', 'ecb', 'gcm', 'ocb'])],
            'cipher_length' => ['required', Rule::in(['128', '192', '256'])],
            'output_type' => ['required', Rule::in(['base64', 'hex'])],
            'data' => ['required'],
            'option' => ['array'],
            'key_format' => ['required', Rule::in(['PKCS8', 'PKCS1'])],
            'key_length' => ['required', Rule::in(['1024', '2048', '3072', '4096'])],
            'is_download' => ['required', 'boolean'],
            'padding' => ['required', Rule::in([
                'ENCRYPTION_OAEP',
                'ENCRYPTION_PKCS1',
                'ENCRYPTION_NONE',
                'SIGNATURE_PSS',
                'SIGNATURE_PKCS1',
            ])],
            'hash' => ['required', Rule::in(['md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224'])],
            'mgf_hash' => [Rule::in(['md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224'])],
        ];
    }

    public function messages(): array
    {
        $knowledgeUrl = [
            'https://stackoverflow.com/questions/48958304/pkcs1-and-pkcs8-format-for-rsa-private-key',
            'https://try8.cn/tool/cipher/rsa',
        ];
        $keyFormatDetailUrlString = implode(',', $knowledgeUrl);
        return [
            'key.required' => '秘钥 key必填',
            'public_key.required' => '公钥 public_key必填',
            'key.string' => '秘钥 key必须为字符串',
            'public_key.string' => '公钥 public_key必须为字符串',
            'cipher_type.required' => '密码学方式 cipher_type 必填',
            'cipher_length.required' => '密码学方式长度 cipher_length 必填',
            'cipher_type.in' => "密码学方式类型 cipher_type 只能为：'cbc', 'ecb', 'gcm', 'ocb'",
            'cipher_length.in' => "密码学方式长度 cipher_length 只能为：'128', '192', '256'",
            'data.required' => 'data 待加解密数据必填',
            'output_type.required' => '转换类型必填',
            'output_type.in' => '转换类型只能是 base64 或者 hex',
            'option.array' => 'option 只能是数组',
            'key_format.required' => 'key_format 秘钥格式必填',
            'key_length.required' => 'key_length 秘钥长度必填',
            'key_format.in' => 'key_format 只能为 PKCS8 或 PKCS1。详情可参见: ' . $keyFormatDetailUrlString,
            'key_length.in' => 'key_length 只能为 1024，2048，3072，4096',
            'is_download.required' => 'is_download 必填',
            'is_download.boolean' => 'is_download 必须为布尔值',
            'padding.required' => 'padding 填充模式必填',
            'padding.in' => 'padding 填充模式必须为：ENCRYPTION_OAEP、ENCRYPTION_PKCS1、ENCRYPTION_NONE、SIGNATURE_PSS、SIGNATURE_PKCS1 之一',
            'hash.required' => 'hash 必填',
            'mgf_hash.required' => 'mgf_hash 必填',
            'hash.in' => "hash 必须为 'md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224' 其中之一",
            'mgf_hash.in' => "mgf_hash 必须为 'md2', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha224' 其中之一",
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

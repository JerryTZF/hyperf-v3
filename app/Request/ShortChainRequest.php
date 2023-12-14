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

class ShortChainRequest extends FormRequest
{
    protected array $scenes = [
        'convert' => ['url', 'ttl'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url'],
            'ttl' => ['integer', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'url 必填',
            'url.url' => 'url 必须为合法的url地址',
            'ttl.integer' => 'ttl 必须为整数',
            'ttl.gt' => 'ttl 必须大于0',
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

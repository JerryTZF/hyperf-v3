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

class LockRequest extends FormRequest
{
    protected array $scenes = [
        'create_order' => ['gid', 'number'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gid' => ['required', 'integer'],
            'number' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'gid.required' => 'gid 商品id必填',
            'gid.integer' => 'gid 商品id必须为整数',
            'number.required' => 'number 购买数量必填',
            'number.integer' => 'number 购买数量必须为整数',
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}

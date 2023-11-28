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
use JetBrains\PhpStorm\ArrayShape;

class TestListRequest extends FormRequest
{
    protected array $scenes = [
    ];

    public function authorize(): bool
    {
        return true;
    }

    #[ArrayShape(['username' => 'string', 'gender' => 'string'])]
    public function rules(): array
    {
        return [
            'username' => 'required',
            'gender' => 'required',
        ];
    }
}

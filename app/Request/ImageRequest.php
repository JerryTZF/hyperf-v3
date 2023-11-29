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

class ImageRequest extends FormRequest
{
    protected array $scenes = [
        'qrcode' => ['is_download', 'logo', 'size', 'margin', 'logo_size', 'content', 'foreground_color', 'background_color', 'mime', 'label_text', 'logo_path'],
        'decode' => ['upload_qrcode', 'qrcode_url'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'size' => ['integer'],
            'margin' => ['integer'],
            'logo_size' => ['integer'],
            'content' => ['string'],
            'foreground_color' => ['array'],
            'background_color' => ['array'],
            'mime' => [Rule::in(['png', 'jpeg', 'jpg', 'bmp'])],
            'label_text' => ['string'],
            'logo' => ['file', 'image'],
            'upload_qrcode' => ['file', 'image'],
            'qrcode_url' => ['url'],
            'is_download' => ['boolean'],
        ];
    }
}

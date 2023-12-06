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
use Picqer\Barcode\BarcodeGenerator;
use ReflectionClass;

class ImageRequest extends FormRequest
{
    /**
     * 场景值
     * @var array|string[][]
     */
    protected array $scenes = [
        'qrcode' => ['is_download', 'logo', 'size', 'margin', 'logo_size', 'content', 'foreground_color', 'background_color', 'mime', 'label_text', 'logo_path'],
        'decode' => ['upload_qrcode', 'qrcode_url'],
        'barcode' => ['bar_type', 'height', 'width', 'content'],
        'captcha' => ['captcha_unique_code'],
        'verify' => ['captcha_unique_code', 'captcha'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * 验证规则.
     */
    public function rules(): array
    {
        return [
            'size' => ['integer'],
            'margin' => ['integer'],
            'logo_size' => ['integer'],
            'content' => ['required', 'string'],
            'foreground_color' => ['array'],
            'background_color' => ['array'],
            'mime' => [Rule::in(['png', 'jpeg', 'jpg', 'bmp'])],
            'label_text' => ['string'],
            'logo' => ['file', 'image'],
            'upload_qrcode' => ['file', 'image'],
            'qrcode_url' => ['url'],
            'is_download' => ['boolean'],
            'bar_type' => [Rule::in($this->getBarcodeConstants())],
            'width' => ['integer'],
            'height' => ['integer'],
            'captcha_unique_code' => ['required', 'string'],
            'captcha' => ['required', 'alpha_num'],
        ];
    }

    /**
     * 自定义错误文案.
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'bar_type.in' => '条形码类型必须为：' . implode(',', $this->getBarcodeConstants()) . ' 中',
            'size.integer' => 'size 必须为整数',
            'margin.integer' => 'margin 必须为整数',
            'logo_size.integer' => 'logo_size 必须为整数',
            'content.string' => 'content 必须为字符串',
            'foreground_color.array' => 'foreground_color 必须为数组，例如 [0,0,0]',
            'background_color.array' => 'background_color 必须为数组，例如 [255,255,255]',
            'mime.in' => 'mime 必须为 png, jpeg, jpg, bmp',
            'label_text.string' => 'label_text 必须为字符串',
            'logo.file' => 'logo 必须为上传文件',
            'logo.image' => 'logo 必须为图片类型文件',
            'qrcode_url.url' => 'qrcode_url 必须为合法的url地址',
            'is_download.boolean' => 'boolean 必须为合法的布尔值，例如 1、true、\'true\'、false、\'false\'',
            'width.integer' => 'width 必须为整数',
            'height.integer' => 'height 必须为整数',
            'captcha_unique_code.string' => 'captcha_unique_code 必须为唯一字符串',
            'captcha_unique_code.required' => 'captcha_unique_code 必须必填',
            'captcha.required' => 'captcha 验证码必填',
            'captcha.alpha_num' => 'captcha 验证码必须是字母或数字',
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    /**
     * 获取 Picqer\Barcode\BarcodeGenerator 所有常量.
     */
    private function getBarcodeConstants(): array
    {
        $reflection = new ReflectionClass(BarcodeGenerator::class);
        return array_values($reflection->getConstants());
    }
}

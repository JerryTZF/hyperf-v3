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

namespace App\Lib\Validator;

use Hyperf\Validation\Rule;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ImageValidator extends AbstractValidator
{
    public static function validateQrcodeConfig(array $data, $message = []): bool
    {
        try {
            $rules = [
                'size' => ['integer', 'max:500'],
                'margin' => ['integer', 'max:50'],
                'logo_size' => ['integer', 'max:100'],
                'label_text' => ['alpha_dash'],
                'mime' => [Rule::in(['png', 'svg', 'pdf', 'gif'])],
                'foreground_color' => ['array'],
                'background_color' => ['array'],
                'content' => ['required'],
            ];
            $message = empty($message) ? [
                'size.integer' => 'size 必须为整数',
                'size.max' => 'size 最大500px',
                'margin.max' => 'margin 最大50px',
                'margin.integer' => 'margin 必须为整数',
                'logo_size.integer' => 'logo_size 必须为整数',
                'logo_size.max' => 'logo_size 最大50px',
                'label_text.alpha_dash' => 'label 可以含有字母、数字，短破折号（-）和下划线（_）',
                'mime.in' => 'mime 必须为 png, svg, pdf, gif',
                'foreground_color.array' => 'foreground_color 必须为数组',
                'background_color.array' => 'background_color 必须为数组',
                'content.required' => 'content 必填',
            ] : [];
            return self::make($data, $rules, $message);
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface $e) {
            return false;
        }
    }
}

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

namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;

// 自定义验证器规则监听器注册
#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var ValidatorFactoryInterface $factory */
        $factory = $event->validatorFactory;
        // 注册手机号验证规则
        $factory->extend('phone', function ($attr, $value, $parameters, $validator) {
            return (bool) preg_match('/^1[234578]\\d{9}$/', (string) $value);
        });

        // 错误信息占位符
        $factory->replacer('phone', function ($message, $attr, $rule, $parameters) {
            return str_replace(':phone', $attr, $message);
        });
    }
}

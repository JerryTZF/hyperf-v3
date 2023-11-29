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

namespace App\Lib\Image;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;

class Captcha
{
    private Redis $redis;

    public function __construct()
    {
        $this->redis = ApplicationContext::getContainer()->get(Redis::class);
    }

    /**
     * 获取验证
     * @param string $clientUniqueCode 不同的客户端使用不同的唯一标识
     */
    public function getStream(string $clientUniqueCode): string
    {
        // 验证码长度和范围
        $phrase = new PhraseBuilder(4, 'abcdefghijklmnpqrstuvwxyz123456789');
        // 初始化验证码
        $builder = new CaptchaBuilder(null, $phrase);
        // 创建验证码
        $builder->build();
        // 获取验证码内容
        $phrase = $builder->getPhrase();
        $this->redis->del($clientUniqueCode);
        $this->redis->set($clientUniqueCode, $phrase, ['NX', 'EX' => 300]);

        return $builder->get();
    }

    /**
     * 验证验证码
     */
    public function verify(string $captcha, string $clientUniqueCode): bool
    {
        $cachedCaptcha = $this->redis->get($clientUniqueCode);
        if ($cachedCaptcha === $captcha) {
            $this->redis->del($clientUniqueCode);
            return true;
        }
        return false;
    }
}

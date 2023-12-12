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

namespace App\Lib\Alibaba;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Darabonba\OpenApi\Models\Config;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;

use function Hyperf\Support\env;

/**
 * 阿里云短信服务SMS.
 * Class Sms.
 */
class Sms
{
    /**
     * 客户端实例.
     * @var Dysmsapi 客户端实例
     */
    protected Dysmsapi $client;

    /**
     * 可用签名列表.
     * @var array|string[] 可用签名列表
     */
    protected array $signList = ['追风雨'];

    /**
     * Redis实例.
     * @var mixed|Redis redis实例
     */
    protected Redis $redis;

    /**
     * 可用的短信模板.
     * @var array|string[] 模板列表
     */
    protected array $templateCodeMap = [
        'register_scene' => 'SMS_186579493',
        'reset_password' => 'SMS_464225488',
    ];

    /**
     * 用户注册短信验证码存储KEY值(%s为手机号).
     */
    public const SMS_REGISTER_VERIFY_KEY = 'SMS_REGISTER_%s';

    public function __construct(?string $accessKeyId, ?string $accessKeySecret)
    {
        if (is_null($accessKeyId) || is_null($accessKeySecret)) {
            [$accessKeyId, $accessKeySecret] = [env('SMS_ACCESS_ID'), env('SMS_ACCESS_SECRET')];
        }

        $config = new Config([
            // 必填，您的 AccessKey ID
            'accessKeyId' => $accessKeyId,
            // 必填，您的 AccessKey Secret
            'accessKeySecret' => $accessKeySecret,
        ]);
        $this->redis = ApplicationContext::getContainer()->get(Redis::class);
        // Endpoint 请参考 https://api.aliyun.com/product/Dysmsapi
        $config->endpoint = env('SMS_ENDPOINT', 'dysmsapi.aliyuncs.com');
        $this->client = new Dysmsapi($config);
    }

    /**
     * 注册场景的短信验证.
     * @param string $phoneNumber 合法的手机号
     * @return array[] 发送结果
     */
    public function sendSmsForRegister(string $phoneNumber): array
    {
        $random = mt_rand(100000, 999999);
        $key = sprintf(self::SMS_REGISTER_VERIFY_KEY, $phoneNumber);
        $this->redis->setex($key, 300, $random);
        return $this->sendSms(
            phoneNumbers: $phoneNumber,
            templateCode: $this->templateCodeMap['register_scene'],
            signName: Arr::first($this->signList),
            param: ['code' => $random],
        );
    }

    /**
     * 发送短信.
     * @param string $phoneNumbers 手机号
     * @param string $templateCode 模板码
     * @param string $signName 签名
     * @param array $param 模板变量对应参数
     * @return array []
     */
    private function sendSms(
        string $phoneNumbers,
        string $templateCode,
        string $signName,
        array $param,
    ): array {
        $request = new SendSmsRequest();
        $request->phoneNumbers = $phoneNumbers;
        $request->signName = $signName;
        $request->templateCode = $templateCode;
        $request->templateParam = json_encode($param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response = $this->client->sendSms($request);
        return $response->body->toMap();
    }
}

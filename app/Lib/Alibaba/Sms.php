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

use function Hyperf\Support\env;

/**
 * 阿里云短信服务SMS.
 * 请勿根据场景在此类中封装对应的发送短信方法, 应该在对应的service中调用该基础类.
 * Class Sms.
 */
class Sms
{
    /**
     * 用于注册的短信模板.
     */
    public const SMS_TEMPLATE_REGISTER = 'SMS_186579493';

    /**
     * 用于重置密码的短信模板.
     */
    public const SMS_TEMPLATE_RESET_PWD = 'SMS_464225488';

    /**
     * 可用的签名列表.
     */
    public const SMS_SIGN_LIST = [
        'ZFY' => '追风雨',
    ];

    /**
     * 客户端实例.
     * @var Dysmsapi 客户端实例
     */
    protected Dysmsapi $client;

    public function __construct(string $accessKeyId = null, string $accessKeySecret = null)
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
        // Endpoint 请参考 https://api.aliyun.com/product/Dysmsapi
        $config->endpoint = env('SMS_ENDPOINT', 'dysmsapi.aliyuncs.com');
        $this->client = new Dysmsapi($config);
    }

    /**
     * 发送短信.
     * @param string $phoneNumbers 手机号
     * @param string $templateCode 模板码
     * @param string $signName 签名
     * @param array $param 模板变量对应参数
     * @return array string[][]
     */
    public function sendSms(
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

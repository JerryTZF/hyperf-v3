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
require_once './vendor/autoload.php';

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response;
use Hyperf\Nano\Factory\AppFactory;
use Hyperf\Stringable\Str;

date_default_timezone_set('Asia/Shanghai');

// ---------------------------------------
// | github webhook server
// | 注意: 这里是示例如何用 hyperf-nano 实现
// | github webhook 与该项目并不相关 !!!
// ---------------------------------------

$app = AppFactory::create('0.0.0.0', 9601);
$key = \Hyperf\Support\env('SECRET');

// github webhook
$app->post('/webhook/github', function () use ($key) {
    $response = new Response();
    [$githubEvent, $githubSha1, $githubSha256, $payload, $isUpdateComposer] = [
        $this->request->getHeaderLine('x-github-event'),
        $this->request->getHeaderLine('x-hub-signature'),
        $this->request->getHeaderLine('x-hub-signature-256'),
        $this->request->all(),
        false,
    ];
    // 不是PUSH动作不做处理
    if (strtolower($githubEvent) !== 'push') {
        return $response->withStatus(401)
            ->withHeader('content-type', 'application/json')
            ->withBody(new SwooleStream(json_encode([
                'code' => 401,
                'msg' => '非push操作',
                'status' => false,
                'data' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    }

    // 签名错误
    [$signSha1, $signSha256, $githubSha1, $githubSha256] = [
        hash_hmac('sha1', json_encode($payload, 256 | 64), $key, false),
        hash_hmac('sha256', json_encode($payload, 256 | 64), $key, false),
        Str::after($githubSha1, 'sha1='),
        Str::after($githubSha256, 'sha256='),
    ];
    if ($signSha1 !== $githubSha1 || $signSha256 !== $githubSha256) {
        return $response->withStatus(401)
            ->withHeader('content-type', 'application/json')
            ->withBody(new SwooleStream(json_encode([
                'code' => 401,
                'msg' => '签名错误',
                'status' => false,
                'data' => ['signSha1' => $signSha1, 'signSha256' => $signSha256],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    }

    // 变更的文件如果有composer.json 则更新依赖包
    $commits = $payload['commits'];
    foreach ($commits as $commit) {
        if (in_array('composer.json', $commit['modified'])) {
            $isUpdateComposer = true;
        }
    }

    // 执行脚本命令(异步处理)
    Hyperf\Coroutine\Coroutine::create(function () use ($isUpdateComposer) {
        $command = $isUpdateComposer ?
            'cd /your-hyperf-path/hyperf-v3 && rm -rf ./runtime/container/ && git checkout . && git pull && echo yes | composer update && supervisorctl restart hyperf' :
            'cd /your-hyperf-path/hyperf-v3 && rm -rf ./runtime/container/ && git checkout . && git pull && supervisorctl restart hyperf';
        shell_exec($command);
    });

    return ['code' => 200, 'msg' => 'ok', 'status' => true, 'data' => []];
});

$app->run();

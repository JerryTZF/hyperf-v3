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

namespace App\Scheduler;

use App\Lib\GuzzleHttp\GuzzleFactory;
use App\Lib\Log\Log;
use App\Model\Auths;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Throwable;

#[Crontab(
    rule: '0 * * * *', // 每小时执行一次
    name: 'SyncAuthsTableScheduler',
    onOneServer: true,
    callback: 'execute',
    memo: '每小时同步一次API路由(未写入的写入, 已废弃的移除)',
    enable: 'isEnable',
)]
class SyncAuthsTableScheduler
{
    public function execute(): void
    {
        try {
            $factory = ApplicationContext::getContainer()->get(DispatcherFactory::class);
            $routes = Arr::first($factory->getRouter('http')->getData(), function ($v, $k) {return ! empty($v); });
            $nowDate = Carbon::now()->toDateTimeString();
            $appDomain = \Hyperf\Support\env('APP_DOMAIN');
            $guzzleClient = GuzzleFactory::getCoroutineGuzzleClient();
            foreach ($routes as $key => $value) {
                /** @var Handler $info */
                foreach ($value as $info) {
                    $callback = $info->callback;
                    $where = [
                        'method' => $key,
                        'route' => $info->route,
                        'controller' => $callback[0],
                        'function' => $callback[1],
                    ];
                    /** @var Auths $auth */
                    $auth = Auths::firstOrCreate($where, ['create_time' => $nowDate, 'update_time' => $nowDate]);
                    $uri = $appDomain . $auth->route;
                    try {
                        $guzzleClient->request($key, $uri, ['headers' => ['x-self-called' => 'yes']]);
                    } catch (GuzzleException $e) {
                        // 注意: 有些接口不传参数可能会导致500, 请在接口处进行处理
                        if ($e->getCode() == 404) {
                            $auth->status = Auths::STATUS_BAN;
                            $auth->save();
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        } finally {
            Log::stdout()->info('SyncAuthsTableScheduler 执行完成');
        }
    }

    public function isEnable(): bool
    {
        return false;
//        return \Hyperf\Support\env('APP_ENV', 'dev') === 'pro';
    }
}

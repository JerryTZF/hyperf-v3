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
return [
    /*
    |--------------------------------------------------------------------------
    | 跨域资源共享 (CORS) 配置
    |--------------------------------------------------------------------------
    |
    | 这里可以配置跨域资源共享的相关设置。
    | 更多信息请参考: https://developer.mozilla.org/zh-CN/docs/Web/HTTP/CORS
    |
    */

    // 允许的来源域名
    // '*' 表示允许所有域名
    // 可以指定具体的域名: ['https://example.com', 'https://api.example.com']
    'allow_origins' => ['*'],

    // 允许的 HTTP 方法
    'allow_methods' => [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
        'OPTIONS',
        'HEAD',
    ],

    // 允许的请求头
    'allow_headers' => [
        'Accept',
        'Authorization',
        'Cache-Control',
        'Content-Type',
        'DNT',
        'If-Modified-Since',
        'Keep-Alive',
        'Origin',
        'User-Agent',
        'X-Requested-With',
        'X-CSRF-Token',
        'X-Token',
        'X-API-KEY',
        'X-Client-Version',
    ],

    // 允许浏览器访问的响应头
    'expose_headers' => [
        'Authorization',
        'X-Token',
        'X-API-KEY',
    ],

    // 是否允许携带凭证信息(如 cookies)
    'allow_credentials' => true,

    // 预检请求的缓存时间(秒)
    'max_age' => 86400, // 24小时

    // 是否在非简单请求时自动添加 Vary: Origin 头
    'vary_origin' => true,
];

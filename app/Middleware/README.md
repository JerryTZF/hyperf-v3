# CORS 跨域中间件使用说明

## 简介

这是一个为 Hyperf 3.1 框架设计的跨域资源共享(CORS)中间件，支持灵活的配置选项。

## 功能特性

- ✅ 支持自定义允许的来源域名
- ✅ 支持自定义允许的 HTTP 方法
- ✅ 支持自定义允许的请求头
- ✅ 支持凭证传输(如 cookies)
- ✅ 支持预检请求缓存
- ✅ 支持暴露响应头给浏览器
- ✅ 完整的 OPTIONS 预检请求处理

## 安装和配置

### 1. 中间件已自动注册

CORS 中间件已经在 `config/autoload/middlewares.php` 中注册：

```php
'http' => [
    // 跨域中间件
    CorsMiddleware::class,
    // ... 其他中间件
],
```

### 2. 配置文件

配置文件位于 `config/autoload/cors.php`，可以根据需要进行调整：

```php
return [
    // 允许的来源域名
    'allow_origins' => ['*'], // 或者指定具体域名 ['https://example.com']
    
    // 允许的 HTTP 方法
    'allow_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    
    // 允许的请求头
    'allow_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        // ... 其他需要的头部
    ],
    
    // 是否允许携带凭证
    'allow_credentials' => true,
    
    // 预检请求缓存时间(秒)
    'max_age' => 86400,
];
```

## 使用示例

### 基本使用

中间件会自动处理所有 HTTP 请求的跨域问题，无需额外配置。

### 特定路由使用

如果只想对特定路由启用 CORS，可以在路由配置中单独添加：

```php
use App\Middleware\CorsMiddleware;

Router::get('/api/test', [TestController::class, 'index'], [
    'middleware' => [CorsMiddleware::class]
]);
```

### 环境区分配置

可以在不同环境下使用不同的 CORS 配置：

```php
// config/autoload/cors.php
return [
    'allow_origins' => env('APP_ENV') === 'production' 
        ? ['https://yourdomain.com'] 
        : ['*'],
    // ... 其他配置
];
```

## 常见问题

### 1. 凭证问题

当 `allow_credentials` 设置为 `true` 时：
- `allow_origins` 不能设置为 `'*'`
- 必须指定具体的域名

### 2. 预检请求处理

中间件会自动处理 OPTIONS 预检请求并返回 204 状态码。

### 3. 自定义头部

如果前端需要发送自定义头部，需要在 `allow_headers` 中添加相应的头部名称。

## 测试

可以通过以下方式测试 CORS 配置是否生效：

```bash
# 测试预检请求
curl -X OPTIONS \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type, Authorization" \
  http://localhost:9501/api/test

# 测试实际请求
curl -X POST \
  -H "Origin: http://localhost:3000" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer token" \
  http://localhost:9501/api/test
```

## 注意事项

1. 生产环境中建议明确指定允许的域名，而不是使用通配符 `*`
2. 如果使用了 JWT 或其他认证机制，确保正确配置 `allow_headers`
3. 预检请求缓存时间根据实际需求调整
4. 当允许凭证传输时，注意安全性考虑

## 相关链接

- [MDN CORS 文档](https://developer.mozilla.org/zh-CN/docs/Web/HTTP/CORS)
- [Hyperf 官方文档](https://hyperf.wiki)
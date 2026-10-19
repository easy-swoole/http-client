# EasySwoole HTTP Client

基于 `Swoole\Coroutine\Http\Client` 的轻量级协程 HTTP/WebSocket 客户端，支持常见 HTTP 方法、文件上传与下载、Cookie、代理、TLS 和重定向。

## 环境要求

- PHP 8.1 或更高版本
- Swoole 扩展
- Composer
- HTTP 请求需要运行在 Swoole 协程环境中

## 安装

```bash
composer require easyswoole/http-client
```

## 快速开始

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use EasySwoole\HttpClient\HttpClient;
use Swoole\Coroutine;

Coroutine\run(function () {
    $client = new HttpClient('https://example.com/api/users?page=1');
    $response = $client->get(['Accept' => 'application/json']);

    if ($response->getStatusCode() !== 200) {
        printf(
            "request failed: status=%s error=%s (%s)\n",
            $response->getStatusCode(),
            $response->getErrMsg(),
            $response->getErrCode()
        );
        return;
    }

    var_dump($response->json(true));
});
```

## 发起请求

### GET 与查询参数

`setQuery()` 会合并已有查询参数，新值优先：

```php
$client = new HttpClient('https://example.com/search?lang=zh');
$client->setQuery([
    'lang' => 'en',
    'keyword' => 'swoole',
]);

$response = $client->get();
// 请求路径：/search?lang=en&keyword=swoole

$client->setPath('/v2/search');
```

### POST、PUT、PATCH 与 OPTIONS

```php
// 表单数据
$response = (new HttpClient('https://example.com/form'))->post([
    'name' => 'EasySwoole',
    'enabled' => 1,
]);

// 原始请求体
$response = (new HttpClient('https://example.com/raw'))->put('raw body');
$response = (new HttpClient('https://example.com/raw'))->patch('patch body');

// OPTIONS
$response = (new HttpClient('https://example.com/resource'))->options(
    null,
    ['Access-Control-Request-Method' => 'POST']
);
```

`get()`、`head()`、`trace()` 和 `delete()` 的第一个参数是本次请求要合并的 Header；`put()`、`post()`、`patch()` 和 `options()` 的第一个参数是请求体，第二个参数是 Header。

### JSON 与 XML

`postJson()` 和 `postXml()` 接收已经编码好的字符串，并自动设置对应的 `Content-Type`：

```php
$jsonResponse = (new HttpClient('https://example.com/api'))->postJson(
    json_encode(['name' => 'EasySwoole'], JSON_THROW_ON_ERROR)
);

$xmlResponse = (new HttpClient('https://example.com/xml'))->postXml(
    '<request><name>EasySwoole</name></request>'
);
```

## Header、Cookie 与认证

```php
$client = new HttpClient('https://example.com/profile');

$client->setHeader('X-Request-ID', 'CaseSensitive-ID');
$client->setHeaders([
    'Accept' => 'application/json',
    'X-Client' => 'demo',
]);

$client->addCookie('session', 'token');
$client->addCookies(['locale' => 'zh-CN']);
$client->setBasicAuth('username', 'password');

$response = $client->get();
```

Header 名称会按 HTTP 规则进行大小写归一化，Header 值会保持原样。发生跨 origin 重定向（协议、主机或端口变化）时，客户端会移除 `Authorization`、`Proxy-Authorization` 和 Cookie，避免凭证泄漏。

## 文件上传

兼容 PHP 原生 `CURLFile`，也可以使用本库提供的文件对象：

```php
use EasySwoole\HttpClient\Bean\CURLFile;
use EasySwoole\HttpClient\HttpClient;

$client = new HttpClient('https://example.com/upload');
$response = $client->post([
    'description' => 'avatar',
    'native_file' => new \CURLFile('/path/to/avatar.png'),
    'client_file' => new CURLFile(
        '/path/to/document.pdf',
        'document',
        'application/pdf'
    ),
]);
```

本库 `CURLFile` 的构造参数依次为：本地路径、表单字段名、可选 MIME 类型。可通过 `setFilename()`、`setOffset()` 和 `setLength()` 进一步控制上传信息。

## 文件下载

`download()` 直接把响应体写入文件，适合大文件下载；成功时返回 `Response`，连接或写入失败时返回 `false`。

```php
$client = new HttpClient('https://example.com/archive.zip');
$response = $client->download('/tmp/archive.zip');

if ($response === false) {
    echo "download failed\n";
} else {
    echo $response->getStatusCode();
}

// 从指定偏移量继续下载
$offset = file_exists('/tmp/archive.zip') ? filesize('/tmp/archive.zip') : 0;
$response = $client->download('/tmp/archive.zip', $offset);
```

下载请求同样支持 Cookie、Header 和重定向。

## 重定向

默认最多跟随 3 次重定向，支持 `301`、`302`、`303`、`307` 和 `308`：

```php
$client = new HttpClient('https://example.com/old-path');
$client->setFollowLocation(5);
$response = $client->get();

// 禁止自动重定向
$client->setFollowLocation(0);
```

重定向行为：

- `301/302`：POST 转为 GET，其他方法保持不变。
- `303`：除 HEAD 外转为 GET。
- `307/308`：保留原请求方法和请求体，包括上传文件。
- 支持绝对 URL、协议相对 URL、绝对路径、相对路径和仅查询参数的 `Location`。
- 跨 origin 跳转不会转发认证信息和 Cookie。

## 超时与长连接

```php
$client = new HttpClient('https://example.com/api');
$client
    ->setTimeout(10.0)
    ->setConnectTimeout(3.0)
    ->setReadTimeout(5.0)
    ->setWriteTimeout(5.0)
    ->setKeepAlive(true);
```

也可以直接设置 Swoole 客户端参数：

```php
$client->setClientSetting('defer', false);
$client->setClientSettings([
    'timeout' => 10,
    'keep_alive' => true,
]);
```

## TLS

```php
$client = new HttpClient('https://example.com');
$client->setSslVerifyPeer(true);
$client->setSslHostName('example.com');
$client->setSslCafile('/path/to/ca.pem');

// 双向 TLS
$client->setSslCertFile('/path/to/client.crt');
$client->setSslKeyFile('/path/to/client.key');
$client->setSslPassphrase('secret');
```

还可以使用 `setSslCapath()` 设置 CA 目录，或通过 `setEnableSSL(true)` 强制启用 TLS。

## 代理与本地绑定

```php
$client = new HttpClient('https://example.com');

$client->setProxyHttp('127.0.0.1', 8080, 'user', 'password');
$client->setProxySocks5('127.0.0.1', 1080, 'user', 'password');
$client->setSocketBind('192.168.1.10', 0);
```

## WebSocket

```php
use EasySwoole\HttpClient\HttpClient;
use Swoole\WebSocket\Frame;

$client = new HttpClient('ws://127.0.0.1:9501/chat');

if ($client->upgrade()) {
    $frame = new Frame();
    $frame->data = json_encode(['action' => 'ping']);
    $client->push($frame);

    $reply = $client->recv(3.0);
    var_dump($reply?->data);
}
```

## 响应处理

```php
$response->getStatusCode();
$response->getHeaders();
$response->getBody();
$response->getCookies();
$response->getErrCode();
$response->getErrMsg();

$array = $response->json(true);
$object = $response->json();
$array = $response->jsonp(true);
$xml = $response->xml();
```

网络错误通常表现为非正常状态码（例如 `-1`），可结合 `getErrCode()` 和 `getErrMsg()` 判断具体原因。JSON/XML 解码失败时，相应解析方法会返回 `null` 或 `false`。

## 测试

```bash
composer install
./vendor/bin/co-phpunit --configuration phpunit.xml
```

测试 bootstrap 会自动启动本地 HTTP 和 WebSocket mock 服务，并在测试结束后关闭由它创建的进程。测试覆盖普通请求、文件上传/下载、Cookie、认证、请求复用、WebSocket 以及各种重定向行为。

代理测试默认跳过。如需运行，配置以下环境变量：

```bash
export HTTP_CLIENT_HTTP_PROXY_HOST=127.0.0.1
export HTTP_CLIENT_HTTP_PROXY_PORT=8080
export HTTP_CLIENT_SOCKS5_PROXY_HOST=127.0.0.1
export HTTP_CLIENT_SOCKS5_PROXY_PORT=1080
export HTTP_CLIENT_PROXY_TARGET=http://example.com

./vendor/bin/co-phpunit --configuration phpunit.xml
```

## 主要 API

| 分类 | 方法 |
| --- | --- |
| URL | `setUrl()`、`setPath()`、`setQuery()`、`setEnableSSL()` |
| 请求 | `get()`、`head()`、`trace()`、`delete()`、`put()`、`post()`、`patch()`、`options()`、`postJson()`、`postXml()` |
| Header/Cookie | `setHeader()`、`setHeaders()`、`setCookies()`、`addCookie()`、`addCookies()`、`setBasicAuth()` |
| 内容类型 | `setContentType()`、`setContentTypeJson()`、`setContentTypeXml()`、`setContentTypeFormData()`、`setContentTypeFormUrlencoded()`、`setXMLHttpRequest()` |
| 重定向 | `setFollowLocation()`、`enableFollowLocation()` |
| 超时/连接 | `setTimeout()`、`setConnectTimeout()`、`setReadTimeout()`、`setWriteTimeout()`、`setKeepAlive()` |
| TLS | `setSslVerifyPeer()`、`setSslHostName()`、`setSslCafile()`、`setSslCapath()`、`setSslCertFile()`、`setSslKeyFile()`、`setSslPassphrase()` |
| 代理/网络 | `setProxyHttp()`、`setProxySocks5()`、`setSocketBind()` |
| 下载 | `download()` |
| WebSocket | `upgrade()`、`push()`、`recv()` |
| 底层配置 | `setClientSetting()`、`setClientSettings()`、`getClient()` |

## License

Apache-2.0

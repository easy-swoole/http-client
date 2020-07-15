<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/25
 * Time: 10:43 AM
 */

namespace EasySwoole\HttpClient;

use EasySwoole\HttpClient\Bean\Response;
use EasySwoole\HttpClient\Contract\ClientInterface;
use EasySwoole\HttpClient\Exception\InvalidUrl;
use EasySwoole\HttpClient\Traits\UriManager;
use Swoole\WebSocket\Frame;

class HttpClient
{
    // HTTP 1.0/1.1 标准请求方法
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
    const METHOD_TRACE = 'TRACE';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_OPTIONS = 'OPTIONS';

    // 常用POST提交请求头
    const CONTENT_TYPE_TEXT_XML = 'text/xml';
    const CONTENT_TYPE_TEXT_JSON = 'text/json';
    const CONTENT_TYPE_FORM_DATA = 'multipart/form-data';
    const CONTENT_TYPE_APPLICATION_XML = 'application/xml';
    const CONTENT_TYPE_APPLICATION_JSON = 'application/json';
    const CONTENT_TYPE_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * @var ClientInterface
     */
    protected $clientHandler = \EasySwoole\HttpClient\Handler\Swoole\Client::class;


    /**
     * HttpClient constructor.
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {
        $this->clientHandler = new $this->clientHandler($url);
        $this->setTimeout(3);
        $this->setConnectTimeout(5);
    }

    // --------  客户端配置设置方法  --------

    public function setUrl($url): HttpClient
    {
        /** @see UriManager */
        $this->clientHandler->setUrl($url);
        return $this;
    }

    public function setEnableSSL(bool $enableSSL = true)
    {
        /** @see UriManager */
        $this->clientHandler->setEnableSSL($enableSSL);
    }

    /**
     * @return ClientInterface
     */
    public function getClientHandler(): ClientInterface
    {
        return $this->clientHandler;
    }

    /**
     * @param ClientInterface $clientHandler
     */
    public function setClientHandler(ClientInterface $clientHandler): void
    {
        $this->clientHandler = $clientHandler;
    }

    public function enableFollowLocation(int $maxRedirect = 5): int
    {
        return $this->clientHandler->getRequest()->setFollowLocation($maxRedirect);
    }

    public function setQuery(?array $data = null): HttpClient
    {
        $this->clientHandler->setQuery($data);
        return $this;
    }

    /**
     * 设置请求等待超时时间
     * @param float $timeout 超时时间 单位秒(可传入浮点数指定毫秒)
     * @return HttpClient
     */
    public function setTimeout(float $timeout): HttpClient
    {
        $this->clientHandler->getRequest()->setTimeout($timeout);
        return $this;
    }

    /**
     * 设置连接服务端的超时时间
     * @param float $connectTimeout 超时时间 单位秒(可传入浮点数指定毫秒)
     * @return HttpClient
     */
    public function setConnectTimeout(float $connectTimeout): HttpClient
    {
        $this->clientHandler->getRequest()->setConnectTimeout($connectTimeout);
        return $this;
    }

    /**
     * 启用或关闭HTTP长连接
     * @param bool $keepAlive 是否开启长连接
     * @return $this
     */
    public function setKeepAlive(bool $keepAlive = true)
    {
        $this->clientHandler->getRequest()->setKeepAlive($keepAlive);
        return $this;
    }

    /**
     * 启用或关闭服务器证书验证
     * 可以同时设置是否允许自签证书(默认不允许)
     * @param bool $sslVerifyPeer 是否开启验证
     * @param bool $sslAllowSelfSigned 是否允许自签证书
     * @return HttpClient
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer = true, $sslAllowSelfSigned = false)
    {
        $this->clientHandler->getRequest()->setSslVerifyPeer($sslVerifyPeer, $sslAllowSelfSigned);
        return $this;
    }

    /**
     * 设置服务器主机名称
     * 与ssl_verify_peer配置或Client::verifyPeerCert配合使用
     * @param string $sslHostName 服务器主机名称
     * @return HttpClient
     */
    public function setSslHostName(string $sslHostName)
    {
        $this->clientHandler->getRequest()->setSslHostName($sslHostName);
        return $this;
    }

    /**
     * 设置验证用的SSL证书
     * @param string $sslCafile 证书文件路径
     * @return $this
     */
    public function setSslCafile(string $sslCafile)
    {
        $this->clientHandler->getRequest()->setSslCafile($sslCafile);
        return $this;
    }

    /**
     * 设置SSL证书目录(验证用)
     * @param string $sslCapath 证书目录
     * @return $this
     */
    public function setSslCapath(string $sslCapath)
    {
        $this->clientHandler->getRequest()->setSslCapath($sslCapath);
        return $this;
    }

    /**
     * 设置请求使用的证书文件
     * @param string $sslCertFile 证书文件路径
     * @return $this
     */
    public function setSslCertFile(string $sslCertFile)
    {
        $this->clientHandler->getRequest()->setSslCertFile($sslCertFile);
        return $this;
    }

    /**
     * 设置请求使用的证书秘钥文件
     * @param string $sslKeyFile 秘钥文件路径
     * @return $this
     */
    public function setSslKeyFile(string $sslKeyFile)
    {
        $this->clientHandler->getRequest()->setSslKeyFile($sslKeyFile);
        return $this;
    }

    /**
     * 设置HTTP代理
     * @param string $proxyHost 代理地址
     * @param int $proxyPort 代理端口
     * @param string|null $proxyUser 鉴权用户(非必须)
     * @param string|null $proxyPass 鉴权密码(非必须)
     * @return $this
     */
    public function setProxyHttp(string $proxyHost, int $proxyPort, string $proxyUser = null, string $proxyPass = null)
    {
        $this->clientHandler->getRequest()->setProxyHttp($proxyHost, $proxyPort, $proxyUser, $proxyPass);
        return $this;
    }

    /**
     * 设置Socks5代理
     * @param string $proxyHost 代理地址
     * @param int $proxyPort 代理端口
     * @param string|null $proxyUser 鉴权用户(非必须)
     * @param string|null $proxyPass 鉴权密码(非必须)
     * @return HttpClient
     */
    public function setProxySocks5(string $proxyHost, int $proxyPort, string $proxyUser = null, string $proxyPass = null)
    {
        $this->clientHandler->getRequest()->setProxySocks5($proxyHost, $proxyPort, $proxyUser, $proxyPass);
        return $this;
    }

    /**
     * 设置端口绑定
     * 用于客户机有多张网卡的时候
     * 设置本客户端底层Socket使用哪张网卡和端口进行通讯
     * @param string $bindAddress 需要绑定的地址
     * @param integer $bindPort 需要绑定的端口
     * @return HttpClient
     */
    public function setSocketBind(string $bindAddress, int $bindPort)
    {
        $this->clientHandler->getRequest()->setSocketBind($bindAddress, $bindPort);
        return $this;
    }

    /**
     * 直接设置客户端配置
     * @param string $key 配置key值
     * @param mixed $setting 配置value值
     * @return HttpClient
     */
    public function setClientSetting(string $key, $setting): HttpClient
    {
        $this->clientHandler->getRequest()->setClientSetting($key, $setting);
        return $this;
    }

    /**
     * 直接批量设置客户端配置
     * @param array $settings 需要设置的配置项
     * @param bool $isMerge 是否合并设置(默认直接覆盖配置)
     * @return HttpClient
     */
    public function setClientSettings(array $settings, $isMerge = true): HttpClient
    {
        $this->clientHandler->getRequest()->setClientSettings($settings, $isMerge);
        return $this;
    }

    public function setBasicAuth(string $userName, string $password): HttpClient
    {
        $this->clientHandler->getRequest()->setBasicAuth($userName, $password);
        return $this;
    }

    public function getClient()
    {
        return $this->clientHandler->getClient();
    }

    public function setMethod(string $method): HttpClient
    {
        $this->clientHandler->getRequest()->setMethod($method);
        return $this;
    }

    /**
     * 设置为XMLHttpRequest请求
     * @return $this
     */
    public function setXMLHttpRequest()
    {
        $this->setHeader('x-requested-with', 'xmlhttprequest');
        return $this;
    }

    /**
     * 设置为Json请求
     * @return $this
     */
    public function setContentTypeJson()
    {
        $this->setContentType(HttpClient::CONTENT_TYPE_APPLICATION_JSON);
        return $this;
    }

    /**
     * 设置为Xml请求
     * @return $this
     */
    public function setContentTypeXml()
    {
        $this->setContentType(HttpClient::CONTENT_TYPE_APPLICATION_XML);
        return $this;
    }

    /**
     * 设置为FromData请求
     * @return $this
     */
    public function setContentTypeFormData()
    {
        $this->setContentType(HttpClient::CONTENT_TYPE_FORM_DATA);
        return $this;
    }

    /**
     * 设置为FromUrlencoded请求
     * @return $this
     */
    public function setContentTypeFormUrlencoded()
    {
        $this->setContentType(HttpClient::CONTENT_TYPE_X_WWW_FORM_URLENCODED);
        return $this;
    }

    /**
     * 设置ContentType
     * @param string $contentType
     * @return HttpClient
     */
    public function setContentType(string $contentType)
    {
        $this->setHeader('content-type', $contentType);
        return $this;
    }

    /**
     * 进行一次RAW请求
     * 此模式下直接发送Raw数据需要手动组装
     * 请注意此方法会忽略设置的POST数据而使用参数传入的RAW数据
     * @param string $httpMethod 请求使用的方法 默认为GET
     * @param null $rawData 请注意如果需要发送JSON或XML需要自己先行编码
     * @param string $contentType 请求类型 默认不去设置
     * @return Response
     * @throws InvalidUrl
     */
    protected function rawRequest($httpMethod = HttpClient::METHOD_GET, $rawData = null, $contentType = null): Response
    {
        return $this->clientHandler->rawRequest($httpMethod, $rawData, $contentType);
    }

    /**
     * 快速发起GET请求
     * 设置的请求头会合并到本次请求中
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function get(array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_GET);
    }

    /**
     * 快速发起HEAD请求
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function head(array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_HEAD);

    }

    /**
     * 快速发起TRACE请求
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function trace(array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_TRACE);

    }

    /**
     * 快速发起DELETE请求
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function delete(array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_DELETE);

    }

    // --------  以下四种方法可以设置请求BODY数据  --------

    /**
     * 快速发起PUT请求
     * @param null $data
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function put($data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_PUT, $data);
    }

    /**
     * 快速发起POST请求
     * @param null $data
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function post($data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_POST, $data);
    }

    /**
     * 快速发起PATCH请求
     * @param null $data
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function patch($data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_PATCH, $data);
    }

    /**
     * 快速发起预检请求
     * 需要自己设置预检头部
     * @param null $data
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function options($data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_OPTIONS, $data);
    }

    // -------- 针对POST方法另外给出两种快捷POST  --------

    /**
     * 快速发起XML POST
     * @param string $data 数据需要自己先行转为字符串
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function postXml(string $data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_POST, $data, HttpClient::CONTENT_TYPE_APPLICATION_XML);
    }

    /**
     * 快速发起JSON POST
     * @param string $data 数据需要自己先行转为字符串
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function postJson(string $data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_POST, $data, HttpClient::CONTENT_TYPE_APPLICATION_JSON);
    }

    /**
     * 文件下载直接落盘不走Body拼接更节省内存
     * 可以通过偏移量(offset=原文件字节数)实现APPEND的效果
     * @param string $filename 文件保存到路径
     * @param int $offset 写入偏移量 (设0时如文件已存在底层会自动清空此文件)
     * @param string $httpMethod 设置请求的HTTP方法
     * @param null $rawData 设置请求数据
     * @param null $contentType 设置请求类型
     * @return Response|false 当文件打开失败或feek失败时会返回false
     * @throws InvalidUrl
     */
    public function download(string $filename, int $offset = 0, $httpMethod = HttpClient::METHOD_GET, $rawData = null, $contentType = null)
    {
        return $this->clientHandler->download($filename, $offset, $httpMethod, $rawData, $contentType);
    }


    /**
     * 设置请求头集合
     * @param array $header
     * @param bool $isMerge
     * @param bool strtolower
     * @return HttpClient
     */
    public function setHeaders(array $header, $isMerge = true, $strtolower = true): HttpClient
    {
        $this->clientHandler->getRequest()->setHeaders($header, $isMerge, $strtolower);
        return $this;
    }

    /**
     * 设置单个请求头
     * 根据 RFC 请求头不区分大小写 会全部转成小写
     * @param string $key
     * @param string $value
     * @param bool strtolower
     * @return HttpClient
     */
    public function setHeader(string $key, string $value, $strtolower = true): HttpClient
    {
        $this->clientHandler->getRequest()->setHeader($key, $value, $strtolower);
        return $this;
    }


    /**
     * 设置携带的Cookie集合
     * @param array $cookies
     * @param bool $isMerge
     * @return HttpClient
     */
    public function addCookies(array $cookies, $isMerge = true): HttpClient
    {
        $this->clientHandler->getRequest()->addCookies($cookies, $isMerge);
        return $this;
    }

    /**
     * 设置携带的Cookie
     * @param string $key
     * @param string $value
     * @return HttpClient
     */
    public function addCookie(string $key, string $value): HttpClient
    {
        $this->clientHandler->getRequest()->addCookie($key, $value);
        return $this;
    }

    /**
     * 升级为Websocket请求
     * @param bool $mask
     * @return bool
     * @throws InvalidUrl
     */
    public function upgrade(bool $mask = true): bool
    {
        return $this->clientHandler->upgrade($mask);
    }

    /**
     * 发送数据（websocket）
     * @param string|Frame $data
     * @param int $opcode
     * @param bool $finish
     * @return bool
     */
    public function push($data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
    {
        return $this->clientHandler->push($data, $opcode, $finish);
    }

    /**
     * 接受数据
     * 请注意如果持续接受需要自己处理while(true)逻辑
     * @param float $timeout
     * @return Frame
     */
    public function recv(float $timeout = 1.0)
    {
        return $this->clientHandler->recv($timeout);
    }

    function __destruct()
    {
        if ($this->clientHandler instanceof ClientInterface) {
            $this->clientHandler->closeClient();
            $this->clientHandler = null;
        }
    }
}

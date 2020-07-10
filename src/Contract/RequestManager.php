<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Contract;


use EasySwoole\HttpClient\HttpClient;

interface RequestManager
{
    /**
     * 设置请求等待超时时间
     * @param float $timeout 超时时间 单位秒(可传入浮点数指定毫秒)
     */
    public function setTimeout(float $timeout);

    /**
     * 设置连接服务端的超时时间
     * @param float $connectTimeout 超时时间 单位秒(可传入浮点数指定毫秒)
     */
    public function setConnectTimeout(float $connectTimeout);

    /**
     * 启用或关闭HTTP长连接
     * @param bool $keepAlive 是否开启长连接
     */
    public function setKeepAlive(bool $keepAlive = true);

    /**
     * 启用或关闭服务器证书验证
     * 可以同时设置是否允许自签证书(默认不允许)
     * @param bool $sslVerifyPeer 是否开启验证
     * @param bool $sslAllowSelfSigned 是否允许自签证书
     * @return HttpClient
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer = true, $sslAllowSelfSigned = false);

    /**
     * 设置服务器主机名称
     * 与ssl_verify_peer配置或Client::verifyPeerCert配合使用
     * @param string $sslHostName 服务器主机名称
     */
    public function setSslHostName(string $sslHostName);

    /**
     * 设置验证用的SSL证书
     * @param string $sslCafile 证书文件路径
     */
    public function setSslCafile(string $sslCafile);

    /**
     * 设置SSL证书目录(验证用)
     * @param string $sslCapath 证书目录
     * @return HttpClient
     */
    public function setSslCapath(string $sslCapath);

    /**
     * 设置请求使用的证书文件
     * @param string $sslCertFile 证书文件路径
     */
    public function setSslCertFile(string $sslCertFile);

    /**
     * 设置请求使用的证书秘钥文件
     * @param string $sslKeyFile 秘钥文件路径
     */
    public function setSslKeyFile(string $sslKeyFile);

    /**
     * 设置HTTP代理
     * @param string $proxyHost 代理地址
     * @param int $proxyPort 代理端口
     * @param string|null $proxyUser 鉴权用户(非必须)
     * @param string|null $proxyPass 鉴权密码(非必须)
     */
    public function setProxyHttp(string $proxyHost, int $proxyPort, string $proxyUser = null, string $proxyPass = null);

    /**
     * 设置Socks5代理
     * @param string $proxyHost 代理地址
     * @param int $proxyPort 代理端口
     * @param string|null $proxyUser 鉴权用户(非必须)
     * @param string|null $proxyPass 鉴权密码(非必须)
     */
    public function setProxySocks5(string $proxyHost, int $proxyPort, string $proxyUser = null, string $proxyPass = null);

    /**
     * 设置端口绑定
     * 用于客户机有多张网卡的时候
     * 设置本客户端底层Socket使用哪张网卡和端口进行通讯
     * @param string $bindAddress 需要绑定的地址
     * @param integer $bindPort 需要绑定的端口
     */
    public function setSocketBind(string $bindAddress, int $bindPort);

    /**
     * 直接设置客户端配置
     * @param string $key 配置key值
     * @param mixed $setting 配置value值
     */
    public function setClientSetting(string $key, $setting);

    /**
     * 直接批量设置客户端配置
     * @param array $settings 需要设置的配置项
     * @param bool $isMerge 是否合并设置(默认直接覆盖配置)
     */
    public function setClientSettings(array $settings, $isMerge = true);

    /**
     * Basic Auth
     * @param string $userName
     * @param string $password
     */
    public function setBasicAuth(string $userName, string $password);
}
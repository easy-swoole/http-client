<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Handler\Swoole;


use EasySwoole\HttpClient\Handler\AbstractRequest;
use EasySwoole\HttpClient\HttpClient;

class Request extends AbstractRequest
{

    /**
     * 协程客户端设置项
     * @var array
     */
    protected $clientSetting = [];

    /**
     * @return array
     */
    public function getClientSetting(): array
    {
        return $this->clientSetting;
    }


    public function setTimeout(float $timeout)
    {
        $this->clientSetting['timeout'] = $timeout;
    }

    public function setConnectTimeout(float $connectTimeout)
    {
        $this->clientSetting['connect_timeout'] = $connectTimeout;
    }

    public function setKeepAlive(bool $keepAlive = true)
    {
        $this->clientSetting['keep_alive'] = $keepAlive;
    }

    public function setSslVerifyPeer(bool $sslVerifyPeer = true, $sslAllowSelfSigned = false)
    {
        $this->clientSetting['ssl_verify_peer'] = $sslVerifyPeer;
        $this->clientSetting['ssl_allow_self_signed'] = $sslAllowSelfSigned;
    }

    public function setSslHostName(string $sslHostName)
    {
        $this->clientSetting['ssl_host_name'] = $sslHostName;
    }

    public function setSslCafile(string $sslCafile)
    {
        $this->clientSetting['ssl_cafile'] = $sslCafile;
    }

    public function setSslCapath(string $sslCapath)
    {
        $this->clientSetting['ssl_capath'] = $sslCapath;
    }

    public function setSslCertFile(string $sslCertFile)
    {
        $this->clientSetting['ssl_cert_file'] = $sslCertFile;
    }

    public function setSslKeyFile(string $sslKeyFile)
    {
        $this->clientSetting['ssl_key_file'] = $sslKeyFile;
    }

    public function setProxyHttp(string $proxyHost, int $proxyPort, string $proxyUser = null, string $proxyPass = null)
    {
        $this->clientSetting['http_proxy_host'] = $proxyHost;
        $this->clientSetting['http_proxy_port'] = $proxyPort;

        if (!empty($proxyUser)) {
            $this->clientSetting['http_proxy_user'] = $proxyUser;
        }

        if (!empty($proxyPass)) {
            $this->clientSetting['http_proxy_password'] = $proxyPass;
        }
    }

    public function setProxySocks5(string $proxyHost, int $proxyPort, string $proxyUser = null, string $proxyPass = null)
    {
        $this->clientSetting['socks5_host'] = $proxyHost;
        $this->clientSetting['socks5_port'] = $proxyPort;

        if (!empty($proxyUser)) {
            $this->clientSetting['socks5_username'] = $proxyUser;
        }

        if (!empty($proxyPass)) {
            $this->clientSetting['socks5_password'] = $proxyPass;
        }
    }

    public function setSocketBind(string $bindAddress, int $bindPort)
    {
        $this->clientSetting['bind_address'] = $bindAddress;
        $this->clientSetting['bind_port'] = $bindPort;
    }

    public function setClientSetting(string $key, $setting)
    {
        $this->clientSetting[$key] = $setting;
    }

    public function setClientSettings(array $settings, $isMerge = true)
    {
        if ($isMerge) {  // 合并配置项到当前配置中
            foreach ($settings as $name => $value) {
                $this->clientSetting[$name] = $value;
            }
        } else {
            $this->clientSetting = $settings;
        }
    }



}
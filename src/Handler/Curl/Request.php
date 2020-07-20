<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Handler\Curl;


use EasySwoole\HttpClient\Handler\AbstractRequest;

class Request extends AbstractRequest
{
    /**
     * Curl客户端设置项
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
        $this->clientSetting[CURLOPT_TIMEOUT] = $timeout;
    }

    public function setConnectTimeout(float $connectTimeout)
    {
        $this->clientSetting[CURLOPT_CONNECTTIMEOUT] = $connectTimeout;
    }

    public function setKeepAlive(bool $keepAlive = true)
    {
        $this->clientSetting[CURLOPT_FORBID_REUSE] = $keepAlive;
    }

    public function setSslVerifyPeer(bool $sslVerifyPeer = true, $sslAllowSelfSigned = false)
    {
        $this->clientSetting[CURLOPT_SSL_VERIFYPEER] = $sslVerifyPeer;
    }

    public function setSslHostName(string $sslHostName)
    {
        // 未支持
    }

    public function setSslCafile(string $sslCafile)
    {
        // 未支持
    }

    public function setSslCapath(string $sslCapath)
    {
        // 未支持
    }

    public function setSslCertFile(string $sslCertFile)
    {
        $this->clientSetting[CURLOPT_SSLCERT] = $sslCertFile;
    }

    public function setSslKeyFile(string $sslKeyFile)
    {
        $this->clientSetting[CURLOPT_SSLKEY] = $sslKeyFile;
    }

    public function setProxyHttp(string $proxyHost, int $proxyPort, string $proxyUser = null, string $proxyPass = null)
    {
        $this->clientSetting[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
        $this->clientSetting[CURLOPT_PROXY] = "{$proxyHost}:{$proxyPort}";
        $this->clientSetting[CURLOPT_PROXYUSERPWD] = "{$proxyUser}:{$proxyPass}";
    }

    public function setProxySocks5(string $proxyHost, int $proxyPort, string $proxyUser = null, string $proxyPass = null)
    {
        $this->clientSetting[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
        $this->clientSetting[CURLOPT_PROXY] = "{$proxyHost}:{$proxyPort}";
        $this->clientSetting[CURLOPT_PROXYUSERPWD] = "{$proxyUser}:{$proxyPass}";
    }

    public function setSocketBind(string $bindAddress, int $bindPort)
    {
        // 未支持
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
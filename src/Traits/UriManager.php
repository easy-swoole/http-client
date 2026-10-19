<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */

namespace EasySwoole\HttpClient\Traits;

use EasySwoole\HttpClient\Bean\Url;
use EasySwoole\HttpClient\Exception\InvalidUrl;

trait UriManager
{
    /** @var Url */
    protected $url;


    /**
     * 强制开启SSL请求
     * @var bool
     */
    protected $enableSSL = false;

    /**
     * 设置当前要请求的URL
     * @param string $url 需要请求的网址
     * @return $this
     * @throws InvalidUrl
     */
    public function setUrl($url): self
    {
        if (empty($url)) {
            return $this;
        }

        if ($url instanceof Url) {
            $this->url = $url;
            return $this;
        }
        $info = parse_url($url);
        if ($info === false) {
            throw new InvalidUrl("HttpClient: {$url} is invalid");
        }
        if (empty($info['scheme'])) {
            $info = parse_url('//' . $url); // 防止无scheme导致的host解析异常 默认作为http处理
        }
        if ($info === false || empty($info['host']) || !$this->isValidUrlHost($info['host'])) {
            throw new InvalidUrl("HttpClient: {$url} is invalid");
        }
        if (isset($info['port']) && ($info['port'] < 1 || $info['port'] > 65535)) {
            throw new InvalidUrl("HttpClient: {$url} contains an invalid port");
        }
        $this->url = new Url($info);
        if (empty($this->url->getHost())) {
            throw new InvalidUrl("HttpClient: {$url} is invalid");
        }
        return $this;
    }

    private function isValidUrlHost(string $host): bool
    {
        if (str_starts_with($host, '[') || str_ends_with($host, ']')) {
            return str_starts_with($host, '[')
                && str_ends_with($host, ']')
                && filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        }

        return preg_match('/^[^\s\/?#\[\]]+$/u', $host) === 1;
    }

    public function setPath(?string $path = null)
    {
        $this->url->setPath($path);
        return $this;
    }

    public function setQuery(?array $data = null)
    {
        if ($data) {
            $oldQuery = $this->url->getQuery();
            $old = [];
            if (!empty($oldQuery)) {
                parse_str($oldQuery, $old);
            }
            $this->url->setQuery(http_build_query($data + $old));
        }
        return $this;
    }


    /**
     * 解析当前的请求Url
     * @throws InvalidUrl
     */
    protected function parserUrlInfo()
    {
        // 请求时当前对象没有设置Url
        if (!($this->url instanceof Url)) {
            throw new InvalidUrl("HttpClient: Url is empty");
        }

        // 获取当前的请求参数
        $path = $this->url->getPath();
        $host = $this->url->getHost();
        $port = $this->url->getPort();
        $query = $this->url->getQuery();
        $scheme = strtolower($this->url->getScheme());
        if (empty($scheme)) {
            $scheme = 'http';
        }
        // 支持的scheme
        $allowSchemes = ['http' => 80, 'https' => 443, 'ws' => 80, 'wss' => 443];

        // 只允许进行支持的请求
        if (!array_key_exists($scheme, $allowSchemes)) {
            throw new InvalidUrl("HttpClient: Clients are only allowed to initiate HTTP(WS) or HTTPS(WSS) requests");
        }

        // URL即使解析成功了也有可能存在HOST为空的情况
        if (empty($host)) {
            throw new InvalidUrl("HttpClient: Current URL is invalid because HOST is empty");
        }

        // 如果端口是空的 那么根据协议自动补全端口 否则使用原来的端口
        if (empty($port)) {
            $port = isset($allowSchemes[$scheme]) ? $allowSchemes[$scheme] : 80;
            $this->url->setPort($port);
        }

        // 如果当前是443端口 或者enableSSL 则开启SSL安全链接
        if ($this->enableSSL || $port === 443) {
            $this->url->setIsSsl(true);
        }

        // 格式化路径和查询参数
        $path = empty($path) ? '/' : $path;
        $query = empty($query) ? '' : '?' . $query;
        $this->url->setFullPath($path . $query);
        return $this->url;
    }


    /**
     * 强制开启SSL
     * @param bool $enableSSL
     */
    public function setEnableSSL(bool $enableSSL = true)
    {
        $this->enableSSL = $enableSSL;
    }
}

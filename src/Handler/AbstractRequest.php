<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */

namespace EasySwoole\HttpClient\Handler;

use EasySwoole\HttpClient\HttpClient;

abstract class AbstractRequest
{

    /**
     * 请求携带的Cookies
     * @var array
     */
    protected $cookies = [];


    /**
     * 默认请求头
     * @var array
     */
    protected $header = [
        "user-agent" => 'EasySwooleHttpClient/0.1',
        'accept' => '*/*',
        'pragma' => 'no-cache',
        'cache-control' => 'no-cache'
    ];

    protected $followLocation = 3;

    protected $redirected = 0;

    /**
     * 请求方法
     * @var string
     */
    protected $method = HttpClient::METHOD_GET;


    /**
     * @return int
     */
    public function getFollowLocation(): int
    {
        return $this->followLocation;
    }

    /**
     * @param int $followLocation
     * @return int
     */
    public function setFollowLocation(int $followLocation): int
    {
        $this->followLocation = $followLocation;
        return $this->followLocation;
    }

    /**
     * @return int
     */
    public function getRedirected(): int
    {
        return $this->redirected;
    }

    /**
     * @param int $redirected
     */
    public function setRedirected(int $redirected): void
    {
        $this->redirected = $redirected;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * @param array $cookies
     */
    public function setCookies(array $cookies): void
    {
        $this->cookies = $cookies;
    }


    public function setHeaders(array $header, $isMerge = true, $strtolower = true)
    {
        if (empty($header)) {
            return;
        }

        // 非合并模式先清空当前的Header再设置
        if (!$isMerge) {
            $this->header = [];
        }

        foreach ($header as $name => $value) {
            $this->setHeader($name, $value, $strtolower);
        }
    }


    public function setBasicAuth(string $userName, string $password)
    {
        $basicAuthToken = base64_encode("{$userName}:{$password}");
        $this->setHeader('Authorization', "Basic {$basicAuthToken}", false);
    }

    public function setHeader(string $key, string $value, $strtolower = true)
    {
        if ($strtolower) {
            $this->header[strtolower($key)] = strtolower($value);
        } else {
            $this->header[$key] = $value;
        }
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    public function setContentType(string $contentType)
    {
        $this->setHeader('content-type', $contentType);
    }

    public function addCookie(string $key, string $value)
    {
        $this->cookies[$key] = $value;
    }

    public function addCookies(array $cookies, $isMerge = true)
    {

        if ($isMerge) {  // 合并配置项到当前配置中
            foreach ($cookies as $name => $value) {
                $this->cookies[$name] = $value;
            }
        } else {
            $this->cookies = $cookies;
        }
    }
}
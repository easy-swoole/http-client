<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Handler;


use EasySwoole\HttpClient\Bean\Url;
use EasySwoole\HttpClient\Contract\ClientInterface;
use EasySwoole\HttpClient\Exception\InvalidUrl;
use EasySwoole\HttpClient\Handler\Swoole\Request;
use EasySwoole\HttpClient\Traits\UriManager;

abstract class AbstractClient implements ClientInterface
{

    use UriManager;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var AbstractRequest
     */
    protected $request;


    public function __construct(?string $url = null)
    {
        if (!empty($url)) {
            $this->setUrl($url);
        }
    }

    public function getRequest(): AbstractRequest
    {
        if (!$this->request instanceof AbstractRequest) {
            $this->request = new Request();
        }
        return $this->request;
    }


    public function setQuery(?array $data)
    {
        if ($data) {
            $old = $this->url->getQuery();
            parse_str($old, $old);
            $this->url->setQuery(http_build_query($data + $old));
        }
    }

    public function setPath(?string $path = null)
    {
        // 请求时当前对象没有设置Url
        if (!($this->url instanceof Url)) {
            throw new InvalidUrl("HttpClient: Url is empty");
        }

        $this->url->setPath($path);
    }

}
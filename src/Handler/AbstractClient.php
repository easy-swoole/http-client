<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Handler;


use EasySwoole\HttpClient\Bean\Response;
use EasySwoole\HttpClient\Contract\ClientInterface;
use EasySwoole\HttpClient\Handler\Swoole\Request;
use EasySwoole\HttpClient\HttpClient;
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

    abstract public function rawRequest($httpMethod = HttpClient::METHOD_GET, $rawData = null, $contentType = null): Response;
}
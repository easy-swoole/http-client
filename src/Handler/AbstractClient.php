<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Handler;


use EasySwoole\HttpClient\Contract\ClientManager;
use EasySwoole\HttpClient\Contract\RequestManager;
use EasySwoole\HttpClient\Traits\UriManager;

abstract class AbstractClient implements ClientManager
{

    use UriManager;

    /**
     * @var ClientManager
     */
    protected $client;

    /**
     * @var RequestManager
     */
    protected $request;


    public function __construct(?string $url = null)
    {
        if (!empty($url)) {
            $this->setUrl($url);
        }
    }

    abstract public function getRequest(): AbstractRequest;


    public function setQuery(?array $data)
    {
        if ($data) {
            $old = $this->url->getQuery();
            parse_str($old, $old);
            $this->url->setQuery(http_build_query($data + $old));
        }
    }
}
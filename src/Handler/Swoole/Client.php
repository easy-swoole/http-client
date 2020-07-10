<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Handler\Swoole;


use EasySwoole\HttpClient\Bean\Response;
use EasySwoole\HttpClient\Handler\AbstractClient;
use EasySwoole\HttpClient\Handler\AbstractRequest;
use EasySwoole\HttpClient\HttpClient;

class Client extends AbstractClient
{

    /** @var \Swoole\Coroutine\Http\Client */
    protected $client;

    /** @var Request */
    protected $request;

    public function createClient(string $host, int $port = 80, bool $ssl = false)
    {
        $this->client = new \Swoole\Coroutine\Http\Client($host, $port, $ssl);
    }

    public function getRequest(): Request
    {
        if (!$this->request instanceof AbstractRequest) {
            $this->request = new Request();
        }
        return $this->request;
    }

    public function getClient(): \Swoole\Coroutine\Http\Client
    {
        if ($this->client instanceof \Swoole\Coroutine\Http\Client) {
            $url = $this->parserUrlInfo();
            $this->client->host = $url->getHost();
            $this->client->port = $url->getPort();
            $this->client->ssl = $url->getIsSsl();
            $this->client->set($this->request->getClientSetting());
            return $this->client;
        }
        $url = $this->parserUrlInfo();
        $this->createClient($url->getHost(), $url->getPort(), $url->getIsSsl());;
        $this->client->set($this->getRequest()->getClientSetting());
        return $this->getClient();
    }

    public function closeClient(): bool
    {
        if ($this->client instanceof \Swoole\Coroutine\Http\Client) {
            return $this->client->close();
        }
        return false;
    }

    public function upgrade(bool $mask = true): bool
    {
        $this->getRequest()->setClientSetting('websocket_mask', $mask);
        $client = $this->getClient();
        return $client->upgrade($this->url->getFullPath());
    }

    public function push($data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true)
    {
        return $this->getClient()->push($data, $opcode, $finish);
    }

    public function recv(float $timeout = 1.0)
    {
        return $this->getClient()->recv($timeout);
    }

    public function download(string $filename, int $offset = 0, $httpMethod = HttpClient::METHOD_GET, $rawData = null, $contentType = null)
    {

        $client = $this->getClient();
        $client->setMethod($httpMethod);

        // 如果提供了数组那么认为是x-www-form-unlencoded快捷请求
        if (is_array($rawData)) {
            $rawData = http_build_query($rawData);
            $this->request->setContentType(HttpClient::CONTENT_TYPE_X_WWW_FORM_URLENCODED);
        }

        // 直接设置请求包体 (特殊格式的包体可以使用提供的Helper来手动构建)
        if (!empty($rawData)) {
            $client->setData($rawData);
            $this->request->setHeader('Content-Length', strlen($rawData));
        }

        // 设置ContentType(如果未设置默认为空的)
        if (!empty($contentType)) {
            $this->request->setContentType($contentType);
        }

        $response = $client->download($this->url->getFullPath(), $filename, $offset);
        return $response ? $this->createHttpResponse($client) : false;
    }


    private function createHttpResponse(\Swoole\Coroutine\Http\Client $client): Response
    {
        $response = new Response((array)$client);
        $response->setClient($client);
        return $response;
    }

    public function rawRequest($httpMethod = HttpClient::METHOD_GET, $rawData = null, $contentType = null): Response
    {
        $client = $this->getClient();
        //预处理。合并cookie 和header
        $this->request->setMethod($httpMethod);
        $client->setMethod($httpMethod);
        $client->setCookies((array)$this->request->getCookies() + (array)$client->cookies);
        if ($httpMethod == HttpClient::METHOD_POST) {
            if (is_array($rawData)) {
                foreach ($rawData as $key => $item) {
                    if ($item instanceof \CURLFile) {
                        $client->addFile($item->getFilename(), $key, $item->getMimeType(), $item->getPostFilename());
                        unset($rawData[$key]);
                    }
                }
                $client->setData($rawData);
            } else if ($rawData !== null) {
                $client->setData($rawData);
            }
        } else if ($rawData !== null) {
            $client->setData($rawData);
        }
        if (is_string($rawData)) {
            $this->request->setHeader('Content-Length', strlen($rawData));
        }
        if (!empty($contentType)) {
            $this->request->setContentType($contentType);
        }
        $client->setHeaders($this->request->getHeader());
        $client->execute($this->url->getFullPath());
        // 如果不设置保持长连接则直接关闭当前链接
        if (!isset($this->request->getClientSetting()['keep_alive']) || $this->request->getClientSetting()['keep_alive'] !== true) {
            $client->close();
        }
        // 处理重定向
        $redirected = $this->request->getRedirected();
        $followLocation = $this->request->getFollowLocation();
        if (($client->statusCode == 301 || $client->statusCode == 302) && (($followLocation > 0) && ($redirected < $followLocation))) {
            $this->request->setRedirected(++$redirected);
            $location = $client->headers['location'];
            $info = parse_url($location);
            // scheme 为空 没有域名
            if (empty($info['scheme']) && empty($info['host'])) {
                $this->url->setPath($location);
                $this->parserUrlInfo();
            } else {
                // 去除//开头的跳转域名
                $location = ltrim($location, '//');
                $this->setUrl($location);
                $this->client = null;
            }
            return $this->rawRequest($httpMethod, $rawData, $contentType);
        } else {
            $this->request->setRedirected(0);
        }
        return $this->createHttpResponse($client);
    }
}
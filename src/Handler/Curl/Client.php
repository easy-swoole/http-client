<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */

namespace EasySwoole\HttpClient\Handler\Curl;

use EasySwoole\HttpClient\Bean\Response;
use EasySwoole\HttpClient\Handler\AbstractClient;
use EasySwoole\HttpClient\Handler\AbstractRequest;
use EasySwoole\HttpClient\HttpClient;

class Client extends AbstractClient
{
    /** @var resource */
    protected $client;

    public function createClient(string $host, int $port = 80, bool $ssl = false)
    {
        $this->client = curl_init();
        curl_setopt($this->client, CURLOPT_URL, "{$host}:{$port}{$this->url->getFullPath()}");

    }

    public function closeClient()
    {
        $this->client = curl_init();
        if (gettype($this->client) == 'resource') {
            curl_close($this->client);
        }
    }

    public function getClient()
    {
        $url = $this->parserUrlInfo();
        if (gettype($this->client) == 'resource') {
            return $this->client;
        }
        $this->createClient($url->getScheme() . '://' . $url->getHost(), $url->getPort(), $url->getIsSsl());
        return $this->getClient();
    }

    public function setMethod($method)
    {
        $client = $this->getClient();
        curl_setopt($client, CURLOPT_CUSTOMREQUEST, $method);
    }

    public function rawRequest($httpMethod = HttpClient::METHOD_GET, $rawData = null, $contentType = null): Response
    {
        $request = $this->getRequest();
        $request->setMethod($httpMethod);

        $client = $this->getClient();
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_CUSTOMREQUEST, $httpMethod);
        curl_setopt($client, CURLOPT_HEADER, true);

        /**----------------------------------构建请求数据-----------------------------------------*/
        if ($rawData !== null) {
            if (is_array($rawData)) {
                $rawData = http_build_query($rawData);
                $request->setContentType(HttpClient::CONTENT_TYPE_X_WWW_FORM_URLENCODED);
            } elseif (is_string($rawData)) {
                $request->setContentType('text/plain');
                $request->setHeader('Content-Length', strlen($rawData));
            }
            curl_setopt($client, CURLOPT_POSTFIELDS, $rawData);
        }

        /**----------------------------设置content type-----------------------------------------*/
        if (!empty($contentType)) {
            $request->setContentType($contentType);
        }

        /**---------------------------Follow Location------------------*/
        $followLocation = $request->getFollowLocation();
        curl_setopt($client, CURLOPT_FOLLOWLOCATION, $followLocation > 0);
        curl_setopt($client, CURLOPT_MAXREDIRS, $followLocation);

        /**------------------------设置header-----------------------------------------*/
        $headers = [];
        foreach ($request->getHeader() as $k => $v) {
            $headers[] = "{$k}: $v";
        }
        curl_setopt($client, CURLOPT_HTTPHEADER, $headers);

        /**------------------------设置cookie-----------------------------------------*/
        $cookies = '';
        foreach ($request->getCookies() as $k => $v) {
            $cookies .= "{$k}=$v;";
        }
        curl_setopt($client, CURLOPT_COOKIE, $cookies);

        /**------------------------设置opt并执行请求-----------------------------------------*/
        curl_setopt_array($client, $request->getClientSetting());
        $body = curl_exec($client);

        /**------------------------构建响应信息-----------------------------------------*/
        $curlInfo = curl_getinfo($client);
        $headerSize = $curlInfo['header_size'];

        $responseHeader = explode("\n", substr($body, 0, $headerSize));

        array_shift($responseHeader);
        array_pop($responseHeader);
        array_pop($responseHeader);
        $resH = [];
        foreach ($responseHeader as $header) {
            $header = explode(':', $header, 2);
            $resH[trim(current($header))] = trim(next($header));
        }

        $response = new Response([
            'headers' => $resH,
            'body' => substr($body, $headerSize),
            'errorCode' => curl_errno($client),
            'errorMsg' => curl_error($client),
            'statusCode' => $curlInfo['http_code'],
            'cookies' => $request->getCookies(),
            'host' => $this->url->getHost(),
            'port' => $this->url->getPort(),
            'ssl' => $this->url->getIsSsl(),
            'requestMethod' => $httpMethod,
            'requestHeaders' => $request->getHeader(),
            'requestBody' => $rawData,
        ]);
        $response->setClient($client);
        return $response;
    }

    public function getRequest(): AbstractRequest
    {
        if (!$this->request instanceof AbstractRequest) {
            $this->request = new Request();
        }
        return $this->request;
    }
}
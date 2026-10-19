<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/25
 * Time: 10:43 AM
 */

namespace EasySwoole\HttpClient;

use EasySwoole\HttpClient\Bean\CURLFile;
use EasySwoole\HttpClient\Bean\Response;
use EasySwoole\HttpClient\Bean\Url;
use EasySwoole\HttpClient\Exception\InvalidUrl;
use EasySwoole\HttpClient\Traits\Request;
use EasySwoole\HttpClient\Traits\UriManager;
use Swoole\Coroutine\Http\Client as CoroutineClient;
use Swoole\WebSocket\Frame;

class HttpClient
{
    // HTTP 1.0/1.1 标准请求方法
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
    const METHOD_TRACE = 'TRACE';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_OPTIONS = 'OPTIONS';

    // 常用POST提交请求头
    const CONTENT_TYPE_TEXT_XML = 'text/xml';
    const CONTENT_TYPE_TEXT_JSON = 'text/json';
    const CONTENT_TYPE_FORM_DATA = 'multipart/form-data';
    const CONTENT_TYPE_APPLICATION_XML = 'application/xml';
    const CONTENT_TYPE_APPLICATION_JSON = 'application/json';
    const CONTENT_TYPE_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    use UriManager;

    use Request;

    /** @var CoroutineClient */
    protected ?CoroutineClient $client = null;

    /**
     * HttpClient constructor.
     * @param string|null $url
     * @throws InvalidUrl
     */
    public function __construct(?string $url = null)
    {
        $this->setUrl($url);
    }

    /**
     * 设置重定向次数 兼容以前版本的方法名称
     * @param int $maxRedirect
     * @return HttpClient
     */
    public function enableFollowLocation(int $maxRedirect = 5): static
    {
        return $this->setFollowLocation($maxRedirect);
    }



    /**---------------------------------http请求---------------------------------------**/

    /**
     * 快速发起GET请求
     * 设置的请求头会合并到本次请求中
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function get(array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_GET);
    }

    /**
     * 快速发起HEAD请求
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function head(array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_HEAD);

    }

    /**
     * 快速发起TRACE请求
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function trace(array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_TRACE);

    }

    /**
     * 快速发起DELETE请求
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function delete(array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_DELETE);

    }

    // --------  以下四种方法可以设置请求BODY数据  --------

    /**
     * 快速发起PUT请求
     * @param null $data
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function put(mixed $data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_PUT, $data);
    }

    /**
     * 快速发起POST请求
     * @param null $data
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function post(mixed $data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_POST, $data);
    }

    /**
     * 快速发起PATCH请求
     * @param null $data
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function patch(mixed $data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_PATCH, $data);
    }

    /**
     * 快速发起预检请求
     * 需要自己设置预检头部
     * @param null $data
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function options(mixed $data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_OPTIONS, $data);
    }

    // -------- 针对POST方法另外给出两种快捷POST  --------

    /**
     * 快速发起XML POST
     * @param string|null $data 数据需要自己先行转为字符串
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function postXml(?string $data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_POST, $data, HttpClient::CONTENT_TYPE_APPLICATION_XML);
    }

    /**
     * 快速发起JSON POST
     * @param string|null $data 数据需要自己先行转为字符串
     * @param array $headers
     * @return Response
     * @throws InvalidUrl
     */
    public function postJson(?string $data = null, array $headers = []): Response
    {
        return $this->setHeaders($headers)->rawRequest(HttpClient::METHOD_POST, $data, HttpClient::CONTENT_TYPE_APPLICATION_JSON);
    }

    /**
     * 下载文件 download 与 get 方法的不同是 download 收到数据后会写入到磁盘，而不是在内存中对 HTTP Body 进行拼接。因此 download 仅使用小量内存，就可以完成超大文件的下载。
     * @param string $filename
     * @param int $offset
     * @param string $httpMethod
     * @param null $rawData
     * @param null $contentType
     * @return Response|false
     * @throws InvalidUrl
     */
    public function download(
        string $filename,
        int $offset = 0,
        string $httpMethod = HttpClient::METHOD_GET,
        mixed $rawData = null,
        ?string $contentType = null
    ): Response|false
    {
        return $this->downloadRequest($filename, $offset, $httpMethod, $rawData, $contentType);
    }

    private function downloadRequest(
        string $filename,
        int $offset,
        string $httpMethod,
        mixed $rawData,
        ?string $contentType,
        bool $stripEntityHeaders = false
    ): Response|false {
        $client = $this->getClient();
        $client->setMethod($httpMethod);
        $this->applyCookiesToClient($client);

        // 如果提供了数组那么认为是x-www-form-urlencoded快捷请求
        if (is_array($rawData)) {
            $rawData = http_build_query($rawData);
            $contentType = $contentType ?: HttpClient::CONTENT_TYPE_X_WWW_FORM_URLENCODED;
        }

        // 直接设置请求包体 (特殊格式的包体可以使用提供的Helper来手动构建)
        if ($rawData !== null) {
            $client->setData($rawData);
        } else {
            $client->setData('');
        }

        $client->setHeaders($this->prepareRequestHeaders($rawData, $contentType, $stripEntityHeaders));

        $succeeded = $client->download($this->url->getFullPath(), $filename, $offset);
        $this->closeClientWhenNeeded($client);
        if (!$succeeded) {
            $this->setRedirected(0);
            return false;
        }

        if ($this->canFollowRedirect($client)) {
            $this->setRedirected($this->getRedirected() + 1);
            $location = $client->headers['location'] ?? null;
            if (!empty($location)) {
                [$httpMethod, $rawData, $contentType, $stripEntityHeaders] = $this->redirectRequestArguments(
                    $client->statusCode,
                    $httpMethod,
                    $rawData,
                    $contentType
                );
                $this->applyRedirectLocation($location);
                return $this->downloadRequest(
                    $filename,
                    $offset,
                    $httpMethod,
                    $rawData,
                    $contentType,
                    $stripEntityHeaders
                );
            }
        }

        $this->setRedirected(0);
        return $this->createHttpResponse($client);
    }

    /**
     * @param string $httpMethod
     * @param null $rawData
     * @param null $contentType
     * @return Response
     * @throws InvalidUrl
     */
    protected function rawRequest(
        string $httpMethod = HttpClient::METHOD_GET,
        mixed $rawData = null,
        ?string $contentType = null,
        bool $stripEntityHeaders = false
    ): Response
    {
        $client = $this->getClient();
        //预处理。合并cookie 和header
        $this->setMethod($httpMethod);
        $client->setMethod($httpMethod);

        $this->applyCookiesToClient($client);


        if ($httpMethod == HttpClient::METHOD_POST) {
            if (is_array($rawData)) {
                $requestData = $rawData;
                foreach ($requestData as $key => $item) {
                    if ($item instanceof \CURLFile) {
                        $client->addFile($item->getFilename(), $key, $item->getMimeType(), $item->getPostFilename());
                        unset($requestData[$key]);
                    }
                    if ($item instanceof CURLFile) {
                        $client->addFile($item->getPath(), $item->getName(), $item->getType(), $item->getFilename(), $item->getOffset(), $item->getLength());
                        unset($requestData[$key]);
                    }
                }
                $client->setData($requestData);
            } else {
                $client->setData($rawData ?? '');
            }
        } else if ($rawData !== null) {
            $client->setData($rawData);
        } else {
            $client->setData('');
        }

        $headers = $this->prepareRequestHeaders($rawData, $contentType, $stripEntityHeaders);
        if ($headers) {
            $client->setHeaders($headers);
        }

        $client->execute($this->url->getFullPath());
        // 如果不设置保持长连接则直接关闭当前链接
        $this->closeClientWhenNeeded($client);
        // 处理重定向
        $redirected = $this->getRedirected();
        $followLocation = $this->getFollowLocation();
        if ($this->isRedirectStatus($client->statusCode) && (($followLocation > 0) && ($redirected < $followLocation))) {
            $this->setRedirected(++$redirected);
            $location = $client->headers['location'] ?? null;
            if (empty($location)) {
                $this->setRedirected(0);
                return $this->createHttpResponse($client);
            }

            [$httpMethod, $rawData, $contentType, $stripEntityHeaders] = $this->redirectRequestArguments(
                $client->statusCode,
                $httpMethod,
                $rawData,
                $contentType
            );
            $this->applyRedirectLocation($location);
            return $this->rawRequest($httpMethod, $rawData, $contentType, $stripEntityHeaders);
        } else {
            $this->setRedirected(0);
        }
        return $this->createHttpResponse($client);
    }

    protected function redirectRequestArguments(
        int $statusCode,
        string $httpMethod,
        mixed $rawData,
        ?string $contentType
    ): array
    {
        $method = strtoupper($httpMethod);
        $switchToGet = ($statusCode === 303 && $method !== self::METHOD_HEAD)
            || (($statusCode === 301 || $statusCode === 302) && $method === self::METHOD_POST);

        if ($switchToGet) {
            return [self::METHOD_GET, null, null, true];
        }

        return [$httpMethod, $rawData, $contentType, false];
    }

    protected function isRedirectStatus(int $statusCode): bool
    {
        return in_array($statusCode, [301, 302, 303, 307, 308], true);
    }

    private function canFollowRedirect(CoroutineClient $client): bool
    {
        return $this->isRedirectStatus($client->statusCode)
            && $this->getFollowLocation() > 0
            && $this->getRedirected() < $this->getFollowLocation();
    }

    protected function prepareRequestHeaders(
        mixed $rawData,
        ?string $contentType,
        bool $stripEntityHeaders = false
    ): array
    {
        $headers = $this->getHeader();
        if ($stripEntityHeaders) {
            foreach (array_keys($headers) as $name) {
                if (in_array(strtolower($name), ['content-length', 'content-type', 'transfer-encoding'], true)) {
                    unset($headers[$name]);
                }
            }
            return $headers;
        }

        if (is_string($rawData)) {
            $headers['content-length'] = (string)strlen($rawData);
        }
        if (!empty($contentType)) {
            $headers['content-type'] = $contentType;
        }
        return $headers;
    }

    private function applyCookiesToClient(CoroutineClient $client): void
    {
        $cookies = (array)$this->getCookies() + (array)$client->cookies;
        $client->setCookies($cookies);
    }

    private function closeClientWhenNeeded(CoroutineClient $client): void
    {
        if (!isset($this->getClientSetting()['keep_alive']) || $this->getClientSetting()['keep_alive'] !== true) {
            $client->close();
        }
    }

    /**
     * Resolve a Location header against the current request URL.
     *
     * @throws InvalidUrl
     */
    protected function resolveRedirectUrl(string $location): string
    {
        $location = trim($location);
        if ($location === '') {
            throw new InvalidUrl('HttpClient: Redirect location is empty');
        }

        $fragmentPosition = strpos($location, '#');
        if ($fragmentPosition !== false) {
            $location = substr($location, 0, $fragmentPosition);
        }

        $fragmentOnly = $location === '';

        $locationInfo = parse_url($location);
        if ($locationInfo === false) {
            throw new InvalidUrl("HttpClient: Redirect location {$location} is invalid");
        }
        if (!empty($locationInfo['scheme'])) {
            return $location;
        }

        $scheme = strtolower($this->url->getScheme() ?: 'http');
        if (str_starts_with($location, '//')) {
            return $scheme . ':' . $location;
        }

        $host = $this->url->getHost();
        if (str_contains($host, ':') && $host[0] !== '[') {
            $host = '[' . $host . ']';
        }
        $authority = $scheme . '://' . $host;
        $port = $this->url->getPort();
        if (!empty($port) && !(($scheme === 'http' || $scheme === 'ws') && $port === 80)
            && !(($scheme === 'https' || $scheme === 'wss') && $port === 443)) {
            $authority .= ':' . $port;
        }

        if ($fragmentOnly) {
            $query = $this->url->getQuery();
            return $authority . ($this->url->getPath() ?: '/') . (empty($query) ? '' : '?' . $query);
        }

        if (str_starts_with($location, '?')) {
            return $authority . ($this->url->getPath() ?: '/') . $location;
        }

        if (str_starts_with($location, '/')) {
            return $authority . $this->normalizeRedirectPath($location);
        }

        $currentPath = $this->url->getPath() ?: '/';
        $basePath = substr($currentPath, 0, strrpos($currentPath, '/') + 1);
        return $authority . $this->normalizeRedirectPath($basePath . $location);
    }

    protected function normalizeRedirectPath(string $pathAndQuery): string
    {
        $query = '';
        $queryPosition = strpos($pathAndQuery, '?');
        if ($queryPosition !== false) {
            $query = substr($pathAndQuery, $queryPosition);
            $pathAndQuery = substr($pathAndQuery, 0, $queryPosition);
        }

        $segments = [];
        foreach (explode('/', $pathAndQuery) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }
        return '/' . implode('/', $segments) . $query;
    }

    protected function applyRedirectLocation(string $location): void
    {
        $origin = $this->getOrigin($this->url);
        $this->setUrl($this->resolveRedirectUrl($location));
        $this->parserUrlInfo();

        if ($origin !== $this->getOrigin($this->url)) {
            $this->removeSensitiveRedirectCredentials();
        }

        $this->client = null;
    }

    protected function getOrigin(Url $url): string
    {
        $scheme = strtolower($url->getScheme() ?: 'http');
        $port = $url->getPort();
        if (empty($port)) {
            $port = ($scheme === 'https' || $scheme === 'wss') ? 443 : 80;
        }
        return $scheme . '://' . strtolower($url->getHost()) . ':' . $port;
    }

    protected function removeSensitiveRedirectCredentials(): void
    {
        foreach (array_keys($this->header) as $name) {
            if (in_array(strtolower($name), ['authorization', 'cookie', 'proxy-authorization'], true)) {
                unset($this->header[$name]);
            }
        }
        $this->cookies = [];
    }


    /**
     * 获取coroutine client
     * @return CoroutineClient
     * @throws InvalidUrl
     */
    public function getClient(): CoroutineClient
    {
        $url = $this->parserUrlInfo();
        if ($this->client instanceof CoroutineClient) {
            $this->client->host = $url->getHost();
            $this->client->port = $url->getPort();
            $this->client->ssl = $url->getIsSsl();
            $this->client->set($this->getClientSetting());
            return $this->client;
        }
        $this->client = new CoroutineClient($url->getHost(), $url->getPort(), $url->getIsSsl());
        return $this->getClient();
    }


    /**--------------------------------Websocket请求-----------------------------------------------*/

    /**
     * 升级为websocket请求
     * @param bool $mask
     * @return bool
     * @throws InvalidUrl
     */
    public function upgrade(bool $mask = true): bool
    {
        $this->setClientSetting('websocket_mask', $mask);
        $client = $this->getClient();
        $this->getCookies() && $client->setCookies($this->getCookies());
        $this->getHeader() && $client->setHeaders($this->getHeader());
        return $client->upgrade($this->url->getFullPath());
    }

    /**
     * 发送websocket数据
     * @param Frame|string $data
     * @param int $opcode
     * @param bool $finish
     * @return mixed
     * @throws InvalidUrl
     */
    public function push(Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
    {
        return $this->getClient()->push($data, $opcode, $finish);
    }

    /**
     * 接收websocket数据
     * @param float $timeout
     * @return Frame
     * @throws InvalidUrl
     */
    public function recv(float $timeout = 1.0): Frame|false
    {
        return $this->getClient()->recv($timeout);
    }

    private function createHttpResponse(CoroutineClient $client): Response
    {
        $response = new Response((array)$client);
        $response->setClient($client);
        return $response;
    }

    public function setMethod(string $method): static
    {
        $this->getClient()->setMethod($method);
        $this->method = $method;
        return $this;
    }

    public function __destruct()
    {
        if ($this->client instanceof CoroutineClient) {
            $this->client->close();
            $this->client = null;
        }
    }
}

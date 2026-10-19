<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/25
 * Time: 1:28 PM
 */

namespace EasySwoole\HttpClient\Bean;

use EasySwoole\Spl\SplBean;
use Swoole\Coroutine\Http\Client;

/**
 * 标准响应体
 * Class Response
 * @package EasySwoole\HttpClient\Bean
 */
class Response extends SplBean
{
    protected ?array $headers = null;
    protected ?string $body = null;
    protected ?int $errCode = null;
    protected ?string $errMsg = null;
    protected ?int $statusCode = null;
    protected ?array $set_cookie_headers = null;
    protected ?array $cookies = null;
    protected ?bool $connected = null;
    protected ?string $host = null;
    protected ?int $port = null;
    protected ?bool $ssl = null;
    protected ?array $setting = null;
    protected ?string $requestMethod = null;
    protected ?array $requestHeaders = null;
    protected mixed $requestBody = null;
    protected ?array $uploadFiles = null;
    protected ?string $downloadFile = null;
    protected ?int $downloadOffset = null;

    protected ?Client $client = null;

    /**
     * Headers Getter
     * @return mixed
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * Body Getter
     * @return mixed
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * ErrCode Getter
     * @return mixed
     */
    public function getErrCode(): ?int
    {
        return $this->errCode;
    }

    /**
     * ErrMsg Getter
     * @return mixed
     */
    public function getErrMsg(): ?string
    {
        return $this->errMsg;
    }

    /**
     * StatusCode Getter
     * @return mixed
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * SetCookieHeaders Getter
     * @return mixed
     */
    public function getSetCookieHeaders(): ?array
    {
        return $this->set_cookie_headers;
    }

    /**
     * Cookies Getter
     * @return mixed
     */
    public function getCookies(): ?array
    {
        return $this->cookies;
    }

    /**
     * Connected Getter
     * @return mixed
     */
    public function getConnected(): ?bool
    {
        return $this->connected;
    }

    /**
     * Host Getter
     * @return mixed
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Port Getter
     * @return mixed
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Ssl Getter
     * @return mixed
     */
    public function getSsl(): ?bool
    {
        return $this->ssl;
    }

    /**
     * Setting Getter
     * @return mixed
     */
    public function getSetting(): ?array
    {
        return $this->setting;
    }

    /**
     * RequestMethod Getter
     * @return mixed
     */
    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    /**
     * RequestHeaders Getter
     * @return mixed
     */
    public function getRequestHeaders(): ?array
    {
        return $this->requestHeaders;
    }

    /**
     * RequestBody Getter
     * @return mixed
     */
    public function getRequestBody(): mixed
    {
        return $this->requestBody;
    }

    /**
     * UploadFiles Getter
     * @return mixed
     */
    public function getUploadFiles(): ?array
    {
        return $this->uploadFiles;
    }

    /**
     * DownloadFile Getter
     * @return mixed
     */
    public function getDownloadFile(): ?string
    {
        return $this->downloadFile;
    }

    /**
     * DownloadOffset Getter
     * @return mixed
     */
    public function getDownloadOffset(): ?int
    {
        return $this->downloadOffset;
    }

    /**
     * Client Getter
     * @return mixed
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * Client Setter
     * @param mixed $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * 获取json格式的内容
     * @param bool $assoc true返回数组 false返回对象
     * @return mixed
     */
    public function json(bool $assoc = false): mixed
    {
        return json_decode($this->body ?? '', $assoc);
    }

    /**
     * 获取jsonp格式的内容
     * @param bool $assoc true返回数组 false返回对象
     * @return mixed
     */
    public function jsonp(bool $assoc = false): mixed
    {
        $jsonp = trim($this->body ?? '');
        if (isset($jsonp[0]) && $jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $begin = strpos($jsonp, '(');
            $end = strrpos($jsonp, ')');
            if (false !== $begin && false !== $end) {
                $jsonp = substr($jsonp, $begin + 1, $end - $begin - 1);
            }
        }
        return json_decode($jsonp, $assoc);
    }

    /**
     * 获取xml格式的内容
     * @see https://www.w3.org/TR/2008/REC-xml-20081126/#charsets - XML charset range
     * @see http://php.net/manual/en/regexp.reference.escape.php - escape in UTF-8 mode
     * @param bool $assoc true返回数组 false返回对象
     * @return array|object
     */
    public function xml(bool $assoc = false): array|object|false
    {
        $xml = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $this->body ?? '');
        if ($xml === null) {
            return false;
        }

        $useInternalErrors = libxml_use_internal_errors(true);
        try {
            $result = simplexml_load_string(
                $xml,
                'SimpleXMLElement',
                LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
        }

        if ($assoc && $result !== false) {
            $result = (array)$result;
        }
        return $result;
    }
}

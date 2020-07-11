<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */

namespace EasySwoole\HttpClient\Contract;

use EasySwoole\HttpClient\Handler\AbstractRequest;

/**
 * Interface ClientInterface
 */
interface ClientInterface
{
    /**
     * ClientInterface constructor.
     * @param string|null $url
     */
    public function __construct(?string $url = null);

    /**
     * create client
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @return mixed
     */
    public function createClient(string $host, int $port = 80, bool $ssl = false);

    /**
     * get client
     * @return mixed
     */
    public function getClient();

    /**
     * close client
     * @return mixed
     */
    public function closeClient();

    /**
     * @return AbstractRequest
     */
    public function getRequest(): AbstractRequest;
}
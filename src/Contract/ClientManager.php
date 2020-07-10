<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */

namespace EasySwoole\HttpClient\Contract;

interface ClientManager
{
    /**
     * ClientManager constructor.
     * @param string|null $url
     */
    public function __construct(?string $url = null);

    /**
     * 创建连接
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @return mixed
     */
    public function createClient(string $host, int $port = 80, bool $ssl = false);

    /**
     * 获取连接
     * @return mixed
     */
    public function getClient();

    /**
     * 关闭连接
     * @return mixed
     */
    public function closeClient();

    /**
     * @return RequestManager
     */
    public function getRequest(): RequestManager;
}
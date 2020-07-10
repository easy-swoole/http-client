<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Handler\Swoole;


use EasySwoole\HttpClient\Contract\ClientManager;

class Client implements ClientManager
{
    /**
     * @var \Swoole\Coroutine\Http\Client
     */
    protected $client;

    public function createClient(string $host, int $port = 80, bool $ssl = false)
    {
        $this->client = new \Swoole\Coroutine\Http\Client($host, $port, $ssl);
    }


    public function getClient()
    {
        return $this->client;
    }

    public function closeClient(): bool
    {
        if ($this->client instanceof \Swoole\Coroutine\Http\Client) {
            return $this->client->close();
        }
        return false;
    }
}
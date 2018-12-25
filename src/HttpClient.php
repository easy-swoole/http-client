<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/12/25
 * Time: 10:43 AM
 */

namespace EasySwoole\HttpClient;


use EasySwoole\HttpClient\Bean\Url;
use EasySwoole\HttpClient\Exception\InvalidUrl;
use Swoole\Coroutine\Http\Client;

class HttpClient
{
    protected $url;
    protected $header = [
        "User-Agent" => 'EasySwooleHttpClient/0.1',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'no-cache'
    ];
    protected $clientSetting = [];
    protected $swooleHttpClient;
    protected $postData;
    protected $isPost = false;
    /*
     * addFile 方法
     */
    protected $postFiles = [];
    /*
     * addData 方法
     */
    protected $postDataByAddData = [];
    function __construct(?string $url = null)
    {
        if(!empty($url)){
            $this->setUrl($url);
        }
        $this->setTimeout(0.1);
    }

    function setUrl(string $url):HttpClient
    {
        $info = parse_url($url);
        $this->url = new Url($info);
        if(empty($this->url->getHost())){
            throw new InvalidUrl("url: {$url} invalid");
        }
        return $this;
    }

    public function setTimeout(float $connectTimeout):HttpClient
    {
        $this->clientSetting['timeout'] = $connectTimeout;
        return $this;
    }

    public function setClientSettings(array $settings):HttpClient
    {
        $this->clientSetting = $settings;
        return $this;
    }

    public function setClientSetting($key,$setting):HttpClient
    {
        $this->clientSetting[$key] = $setting;
        return $this;
    }

    public function setHeaders(array $header):HttpClient
    {
        $this->header = $header;
        return $this;
    }

    public function setHeader($key,$value):HttpClient
    {
        $this->header[$key] = $value;
        return $this;
    }

    public function getSwooleHttpClient():?Client
    {
        return $this->swooleHttpClient;
    }

    public function post($data = null)
    {
        $this->postData = $data;
        $this->isPost = true;
    }

    public function postJSON(string $json):HttpClient
    {
        $this->setHeader('Content-Type','text/json');
        $this->post($json);
        return $this;
    }

    public function postXML(string $xml):HttpClient
    {
        $this->setHeader('Content-Type','text/xml');
        $this->post($xml);
        return $this;
    }

    public function addFile(string $path, string $name, string $mimeType = null, string $filename = null, int $offset = 0, int $length = 0):HttpClient
    {
        $this->isPost = true;
        $this->postFiles[] = [
            $path,$name,$mimeType,$filename,$offset,$length
        ];
        return $this;
    }

    public function addData(string $data, string $name, string $mimeType = null, string $filename = null):HttpClient
    {
        $this->isPost = true;
        $this->postDataByAddData[] = [
            $data,$name,$mimeType,$filename
        ];
        return $this;
    }

    public function exec(?float $timeout = null)
    {
        if($timeout !== null){
            $this->setTimeout($timeout);
        }
        $client = $this->createClient();
        if($this->isPost){
            foreach ($this->postFiles as $file){
                $client->addFile(...$file);
            }
            foreach ($this->postDataByAddData as $addDatum){
                $client->addData(...$addDatum);
            }
            $client->post($this->url->getPath(),$this->url->getQuery(), $this->postData);
        }else{
            $client->get($this->getUri($this->url->getPath(),$this->url->getQuery()));
        }

        $body =  $client->body;
        var_dump($body);
        $client->close();

    }

    private function createClient():Client
    {
        if($this->url instanceof Url){
            $port = $this->url->getPort();
            $ssl = false;
            if(empty($port)){
                if($this->url->getScheme() == 'https'){
                    $port = 443;
                    $ssl = true;
                }else{
                    $port = 80;
                }
            }
            $cli = new Client($this->url->getHost(), $port, $ssl);
            $cli->set($this->clientSetting);
            $cli->setHeaders($this->header);
            $this->swooleHttpClient = $cli;
            return $this->swooleHttpClient;
        }else{
            throw new InvalidUrl("url is empty");
        }
    }

    private function getUri(string $path, string $query): string
    {
        return !empty($query) ? $path . '?' . $query : $path;
    }
}
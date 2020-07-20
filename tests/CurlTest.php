<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace EasySwoole\HttpClient\Test;


use EasySwoole\HttpClient\Handler\Curl\Client;
use EasySwoole\HttpClient\HttpClient;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    private $url = 'http://default.web.com/index.php?arg1=1&arg2=2';

    public function testGet()
    {
        $client = new HttpClient($this->url, Client::class);
        $client->setQuery(['arg2' => 3, 'q' => 2]);
        $json = $client->get()->json(true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 3, 'q' => 2], $json['GET']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $client->setQuery(['arg2' => 3, 'q' => 2]);
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 3, 'q' => 2], $json['GET']);
    }

    public function testDelete()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $response = $client->delete();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("DELETE", $json['REQUEST_METHOD']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $response = $client->delete();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("DELETE", $json['REQUEST_METHOD']);
    }

    public function testPut()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $response = $client->put('testPut');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("PUT", $json['REQUEST_METHOD']);
        $this->assertEquals("testPut", $json['RAW']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $response = $client->put('testPut');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("PUT", $json['REQUEST_METHOD']);
        $this->assertEquals("testPut", $json['RAW']);
    }

    public function testPost()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $response = $client->post([
            'post1' => 'post1'
        ]);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals(['post1' => 'post1'], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $response = $client->post([
            'post1' => 'post1'
        ]);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals(['post1' => 'post1'], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);
    }


    function testPatch()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $response = $client->patch('testPath');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("PATCH", $json['REQUEST_METHOD']);
        $this->assertEquals("testPath", $json['RAW']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $response = $client->patch('testPath');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("PATCH", $json['REQUEST_METHOD']);
        $this->assertEquals("testPath", $json['RAW']);
    }


    function testOptions()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $response = $client->options(['op' => 'op1'], ['head' => 'headtest']);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("OPTIONS", $json['REQUEST_METHOD']);
        $this->assertEquals("headtest", $json['HEADER']['Head']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $response = $client->options(['op' => 'op1'], ['head' => 'headtest']);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("OPTIONS", $json['REQUEST_METHOD']);
        $this->assertEquals("headtest", $json['HEADER']['Head']);
    }


    function testPostXml()
    {
        $client = new HttpClient($this->url);
        $client->setClientHandler(new Client($this->url));
        $response = $client->postXml('<xml></xml>');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('<xml></xml>', $json['RAW']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $response = $client->postXml('<xml></xml>');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('<xml></xml>', $json['RAW']);
    }

    function testPostJson()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $response = $client->postJson(json_encode(['json' => 'json1']));
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);
        $raw = $json["RAW"];
        $raw = json_decode($raw, true);
        $this->assertEquals(['json' => 'json1'], $raw);


        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $response = $client->postJson(json_encode(['json' => 'json1']));
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);
        $raw = $json["RAW"];
        $raw = json_decode($raw, true);
        $this->assertEquals(['json' => 'json1'], $raw);
    }

    function testPostString()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $response = $client->post('postStr');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals('postStr', $json['RAW']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $response = $client->post('postStr');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals('postStr', $json['RAW']);
    }

    function testSetHeaders()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $client->setHeaders([
            'head1' => 'head1',
            'head2' => 'head2'
        ]);
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("head1", $json['HEADER']['Head1']);
        $this->assertEquals("head2", $json['HEADER']['Head2']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $client->setHeaders([
            'head1' => 'head1',
            'head2' => 'head2'
        ]);
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("head1", $json['HEADER']['Head1']);
        $this->assertEquals("head2", $json['HEADER']['Head2']);

    }

    function testSetHeader()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $client->setHeader('head1', 'head1');
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("head1", $json['HEADER']['Head1']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $client->setHeader('head1', 'head1');
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("head1", $json['HEADER']['Head1']);
    }

    function testAddCookies()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $client->addCookies([
            'cookie1' => 'cookie1',
            'cookie2' => 'cookie2'
        ]);
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("cookie1", $json['COOKIE']['cookie1']);
        $this->assertEquals("cookie2", $json['COOKIE']['cookie2']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $client->addCookies([
            'cookie1' => 'cookie1',
            'cookie2' => 'cookie2'
        ]);
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("cookie1", $json['COOKIE']['cookie1']);
        $this->assertEquals("cookie2", $json['COOKIE']['cookie2']);
    }

    function testAddCookie()
    {
        $client = new HttpClient();
        $client->setClientHandler(new Client($this->url));
        $client->addCookie('cookie1', 'cook');
        $response = $client->get(['head' => 'head']);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals("head", $json['HEADER']['Head']);
        $this->assertEquals("cook", $json['COOKIE']['cookie1']);

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl($this->url);
        $client->addCookie('cookie1', 'cook');
        $response = $client->get(['head' => 'head']);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals("head", $json['HEADER']['Head']);
        $this->assertEquals("cook", $json['COOKIE']['cookie1']);
    }

    public function testProxy()
    {
        $client = new HttpClient('http://www.google.com',Client::class);
        $client->setTimeout(3);
        $client->setProxySocks5('127.0.0.1', '1086');
        $response = $client->get();
        $this->assertEquals('200', $response->getStatusCode());

        $client = new HttpClient('http://www.google.com',Client::class);
        $client->setTimeout(3);
        $client->setProxyHttp('127.0.0.1', '1087');
        $response = $client->get();
        $this->assertEquals('200', $response->getStatusCode());
    }

    public function testFollowLocation()
    {

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl("http://blog.gaobinzhan.com");
        $client->enableFollowLocation(0);
        $response = $client->get();
        $this->assertEquals('301', $response->getStatusCode());

        $client = new HttpClient();
        $client->setClientHandler(new Client());
        $client->setUrl("http://blog.gaobinzhan.com");
        $response = $client->get();
        $this->assertEquals('200', $response->getStatusCode());
    }

    public function testSsl(){
        $client = new HttpClient('https://test.ssl.com',Client::class);
        $client->setSslVerifyPeer(false);
        $client->setSslCertFile('/Users/gaobinzhan/Documents/testSsl/client.crt');
        $client->setSslKeyFile('/Users/gaobinzhan/Documents/testSsl/client.key');
        $response = $client->get();
        $this->assertEquals(200,$response->getStatusCode());
    }

    function testDownload()
    {
        $client = new HttpClient('https://www.easyswoole.com/Images/docNavLogo.png',Client::class);
        $response = $client->download('./test.png',0,'POST');
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals(filesize('./test.png'), curl_getinfo($response->getClient())['download_content_length']);
        @unlink('./test.png');
//
        $client = new HttpClient();
        $client->setClientHandler(new Client('https://www.easyswoole.com/Images/docNavLogo.png'));
        $response = $client->download('./test.png');
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals(filesize('./test.png'), curl_getinfo($response->getClient())['download_content_length']);
        @unlink('./test.png');
    }
}
<?php

namespace EasySwoole\HttpClient\Test;

use EasySwoole\HttpClient\Exception\InvalidUrl;
use EasySwoole\HttpClient\HttpClient;
use PHPUnit\Framework\TestCase;
use Swoole\WebSocket\Frame;

class HttpTest extends TestCase
{
    /*
     * url内容请看 tests/index.php
     */
    private $url = 'http://default.web.com/index.php?arg1=1&arg2=2';

    function testGet()
    {
        $client = new HttpClient($this->url);
        $client->setQuery(['arg2' => 3, 'q' => 2]);
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 3, 'q' => 2], $json['GET']);


        $client = new HttpClient();
        $client->setUrl($this->url);
        $client->setQuery(['arg2' => 3, 'q' => 2]);
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 3, 'q' => 2], $json['GET']);
    }

    function testHead()
    {
        $client = new HttpClient($this->url);
        $response = $client->head();
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals('', $response->getBody());

        $client = new HttpClient();
        $client->setUrl($this->url);
        $response = $client->head();
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals('', $response->getBody());
    }

    function testDelete()
    {
        $client = new HttpClient($this->url);
        $response = $client->delete();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("DELETE", $json['REQUEST_METHOD']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $response = $client->delete();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("DELETE", $json['REQUEST_METHOD']);
    }


    function testPut()
    {
        $client = new HttpClient($this->url);
        $response = $client->put('testPut');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("PUT", $json['REQUEST_METHOD']);
        $this->assertEquals("testPut", $json['RAW']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $response = $client->put('testPut');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("PUT", $json['REQUEST_METHOD']);
        $this->assertEquals("testPut", $json['RAW']);
    }

    function testPost()
    {
        $client = new HttpClient($this->url);
        $response = $client->post([
            'post1' => 'post1'
        ]);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals(['post1' => 'post1'], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);

        $client = new HttpClient();
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
        $client = new HttpClient($this->url);
        $response = $client->patch('testPath');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("PATCH", $json['REQUEST_METHOD']);
        $this->assertEquals("testPath", $json['RAW']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $response = $client->patch('testPath');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("PATCH", $json['REQUEST_METHOD']);
        $this->assertEquals("testPath", $json['RAW']);
    }


    function testOptions()
    {
        $client = new HttpClient($this->url);
        $response = $client->options(['op' => 'op1'], ['head' => 'headtest']);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("OPTIONS", $json['REQUEST_METHOD']);
        $this->assertEquals("headtest", $json['HEADER']['Head']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $response = $client->options(['op' => 'op1'], ['head' => 'headtest']);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("OPTIONS", $json['REQUEST_METHOD']);
        $this->assertEquals("headtest", $json['HEADER']['Head']);
    }


    function testPostXml()
    {
        $client = new HttpClient($this->url);
        $response = $client->postXml('<xml></xml>');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('<xml></xml>', $json['RAW']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $response = $client->postXml('<xml></xml>');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('<xml></xml>', $json['RAW']);
    }

    function testPostJson()
    {
        $client = new HttpClient($this->url);
        $response = $client->postJson(json_encode(['json' => 'json1']));
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);
        $raw = $json["RAW"];
        $raw = json_decode($raw, true);
        $this->assertEquals(['json' => 'json1'], $raw);


        $client = new HttpClient();
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

    function testDownload()
    {
        $client = new HttpClient('https://www.easyswoole.com/Images/docNavLogo.png');
        $response = $client->download('./test.png');
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals(filesize('./test.png'), $response->getHeaders()['content-length']);
        @unlink('./test.png');

        $client = new HttpClient();
        $client->setUrl('https://www.easyswoole.com/Images/docNavLogo.png');
        $response = $client->download('./test.png');
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals(filesize('./test.png'), $response->getHeaders()['content-length']);
        @unlink('./test.png');
    }

    function testPostString()
    {
        $client = new HttpClient($this->url);
        $response = $client->post('postStr');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals('postStr', $json['RAW']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $response = $client->post('postStr');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals('postStr', $json['RAW']);
    }

    function testPostFile()
    {
        $client = new HttpClient($this->url);
        $response = $client->post([
            'post1' => 'post1',
            'file' => new \CURLFile(__FILE__)
        ]);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals(['post1' => 'post1'], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);
        $this->assertEquals('HttpTest.php', $json['FILE']['file']['name']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $response = $client->post([
            'post1' => 'post1',
            'file' => new \CURLFile(__FILE__)
        ]);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals(['post1' => 'post1'], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);
        $this->assertEquals('HttpTest.php', $json['FILE']['file']['name']);
    }

    function testSetHeaders()
    {
        $client = new HttpClient($this->url);
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
        $client = new HttpClient($this->url);
        $client->setHeader('head1', 'head1');
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("head1", $json['HEADER']['Head1']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $client->setHeader('head1', 'head1');
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertEquals("head1", $json['HEADER']['Head1']);
    }

    function testAddCookies()
    {
        $client = new HttpClient($this->url);
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
        $client = new HttpClient($this->url);
        $client->addCookie('cookie1', 'cook');
        $response = $client->get(['head' => 'head']);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals("head", $json['HEADER']['Head']);
        $this->assertEquals("cook", $json['COOKIE']['cookie1']);

        $client = new HttpClient();
        $client->setUrl($this->url);
        $client->addCookie('cookie1', 'cook');
        $response = $client->get(['head' => 'head']);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals("head", $json['HEADER']['Head']);
        $this->assertEquals("cook", $json['COOKIE']['cookie1']);
    }

    /**
     * 测试websocket，需要自己实现websocket服务器
     * testWebsocket
     * @throws \EasySwoole\HttpClient\Exception\InvalidUrl
     * @author tioncico
     * Time: 下午12:26
     */
    function testWebsocket()
    {
        $client = new HttpClient('127.0.0.1:9510');
        $client->setHeader('aaa','bbb');
        $upgradeResult = $client->upgrade('cookie1', 'cook');
        $this->assertIsBool(true, $upgradeResult);
        $recvFrame = $client->recv();
        $this->assertEquals('bbb', json_decode($recvFrame->data,true)['aaa'] ?? '');

        $frame = new Frame();
        $frame->data = json_encode(['action' => 'hello', 'content' => ['a' => 1]]);
        $pushResult = $client->push($frame);
        $this->assertIsBool(true, $pushResult);

        $recvFrame = $client->recv();
        $this->assertIsBool(true, !!$recvFrame);
        $this->assertEquals('call hello with arg:{"a":1}', $recvFrame->data);

        $client = new HttpClient();
        $client->setUrl('127.0.0.1:9510');
        $client->addCookie('ca-1','cookie1');
        $upgradeResult = $client->upgrade('cookie1', 'cook');
        $this->assertIsBool(true, $upgradeResult);


        $recvFrame = $client->recv();
        $this->assertEquals('cookie1', json_decode($recvFrame->data,true)['ca-1'] ?? '');

        $frame = new Frame();
        $frame->data = json_encode(['action' => 'hello', 'content' => ['a' => 1]]);
        $pushResult = $client->push($frame);
        $this->assertIsBool(true, $pushResult);
        $recvFrame = $client->recv();
        $this->assertIsBool(true, !!$recvFrame);
        $this->assertEquals('call hello with arg:{"a":1}', $recvFrame->data);
    }

    function testBasicAuth()
    {
        $httpClient = new HttpClient('127.0.0.1:9510');
        $httpClient->setBasicAuth('admin', '111111');
        $res = $httpClient->post();
        $res = $res->getBody();
        $this->assertEquals('success', $res);

        $httpClient = new HttpClient();
        $httpClient->setUrl('127.0.0.1:9510');
        $httpClient->setBasicAuth('admin', '111111');
        $res = $httpClient->post();
        $res = $res->getBody();
        $this->assertEquals('success', $res);
    }

    function testFollowLocation()
    {
        $httpClient = new HttpClient('https://www.gaobinzhan.com/blog');
        $httpClient->enableFollowLocation(0);
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(301, $status);


        $httpClient = new HttpClient('https://www.gaobinzhan.com/blog');
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(200, $status);

        $httpClient->enableFollowLocation(0);
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(200, $status);


        $httpClient = new HttpClient();
        $httpClient->setUrl('https://www.gaobinzhan.com/blog');
        $httpClient->enableFollowLocation(0);
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(301, $status);


        $httpClient = new HttpClient();
        $httpClient->setUrl('https://www.gaobinzhan.com/blog');
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(200, $status);

        $httpClient->enableFollowLocation(0);
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(200, $status);
    }

    public function testSetPath()
    {
        $httpClient = new HttpClient('https://www.easyswoole.com/Cn/demo.html');
        $res = $httpClient->get();
        $res = $res->getBody();
        $this->assertStringContainsString('基于EasySwoole V3 实现的聊天室', $res);
        $httpClient->setPath('/Cn/Preface/introduction.html');
        $res = $httpClient->get();
        $res = $res->getBody();
        $this->assertStringContainsString('admin@fosuss.com', $res);


        $httpClient = new HttpClient();
        $httpClient->setUrl('https://www.easyswoole.com/Cn/demo.html');
        $res = $httpClient->get();
        $res = $res->getBody();
        $this->assertStringContainsString('基于EasySwoole V3 实现的聊天室', $res);
        $httpClient->setPath('/Cn/Preface/introduction.html');
        $httpClient->setQuery(['a' => 2]);
        $res = $httpClient->get();
        $res = $res->getBody();
        $this->assertStringContainsString('admin@fosuss.com', $res);
        $httpClient->setPath();
        $res = $httpClient->get();
        $res = $res->getBody();
        $this->assertStringContainsString('一种愉悦的开发方式', $res);

    }

    public function testSetMethod()
    {
        $httpClient = new HttpClient('https://www.easyswoole.com/Cn/demo.html');
        $httpClient->setMethod('get');
        $this->assertEquals('get', $httpClient->getClient()->requestMethod);
        $httpClient->setMethod('post');
        $this->assertEquals('post', $httpClient->getClient()->requestMethod);
        $httpClient->setMethod('put');
        $this->assertEquals('put', $httpClient->getClient()->requestMethod);
        $httpClient->setMethod('delete');
        $this->assertEquals('delete', $httpClient->getClient()->requestMethod);
    }
}

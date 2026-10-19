<?php

namespace EasySwoole\HttpClient\Test;

use EasySwoole\HttpClient\Bean\CURLFile;
use EasySwoole\HttpClient\HttpClient;
use PHPUnit\Framework\TestCase;
use Swoole\WebSocket\Frame;

class HttpTest extends TestCase
{
    /*
     * url内容请看 tests/index.php
     */
    private $url = 'http://127.0.0.1:9511/index.php?arg1=1&arg2=2';

    function testGet()
    {
        $client = new HttpClient($this->url);
        $client->setQuery(['arg2' => 3, 'q' => 2]);
        $response = $client->get();
        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody(), true);
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals([], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 3, 'q' => 2], $json['GET']);
    }

    function testHead()
    {
        $client = new HttpClient($this->url);
        $response = $client->head();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getBody());
    }

    function testDelete()
    {
        $client = new HttpClient($this->url);
        $response = $client->delete();
        $json = json_decode($response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("DELETE", $json['REQUEST_METHOD']);
    }


    function testPut()
    {
        $client = new HttpClient($this->url);
        $response = $client->put('testPut');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("PUT", $json['REQUEST_METHOD']);
        $this->assertEquals("testPut", $json['RAW']);
    }

    function testPost()
    {
        $client = new HttpClient($this->url);
        $response = $client->post([
            'post1' => 'post1'
        ]);
        $this->assertEquals(200, $response->getStatusCode());
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
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("PATCH", $json['REQUEST_METHOD']);
        $this->assertEquals("testPath", $json['RAW']);
    }


    function testOptions()
    {
        $client = new HttpClient($this->url);
        $response = $client->options(['op' => 'op1'], ['head' => 'headtest']);
        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody(), true);
        $headers = array_change_key_case($json['HEADER'], CASE_LOWER);
        $this->assertEquals("OPTIONS", $json['REQUEST_METHOD']);
        $this->assertEquals("headtest", $headers['head']);
    }


    function testPostXml()
    {
        $client = new HttpClient($this->url);
        $response = $client->postXml('<xml></xml>');
        $json = json_decode($response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
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
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['json' => 'json1'], $raw);
    }

    function testDownload()
    {
        $filename = sys_get_temp_dir() . '/http-client-' . uniqid('', true) . '.txt';
        $client = new HttpClient('http://127.0.0.1:9511/download.txt');
        $response = $client->download($filename);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(filesize($filename), $response->getHeaders()['content-length']);
        @unlink($filename);
    }

    function testDownloadSendsCookiesAndFollowsRedirects()
    {
        $filename = sys_get_temp_dir() . '/http-client-' . uniqid('', true) . '.json';
        $target = '/index.php?download=1';
        $url = 'http://127.0.0.1:9511/index.php?action=redirect&status=302&location=' . rawurlencode($target);
        $client = new HttpClient($url);
        $client->addCookie('download-session', 'secret');

        $response = $client->download($filename);
        $json = json_decode(file_get_contents($filename), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('secret', $json['COOKIE']['download-session']);
        @unlink($filename);
    }

    function testPostString()
    {
        $client = new HttpClient($this->url);
        $response = $client->post('postStr');
        $this->assertEquals(200, $response->getStatusCode());
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
            'file' => new \CURLFile(__FILE__),
            'file1' => new CURLFile(__FILE__, 'test-file')
        ]);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("POST", $json['REQUEST_METHOD']);
        $this->assertEquals(['post1' => 'post1'], $json['POST']);
        $this->assertEquals(['arg1' => 1, 'arg2' => 2], $json['GET']);
        $this->assertEquals('HttpTest.php', $json['FILE']['file']['name']);
        $this->assertEquals('HttpTest.php', $json['FILE']['test-file']['name']);
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
        $headers = array_change_key_case($json['HEADER'], CASE_LOWER);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("head1", $headers['head1']);
        $this->assertEquals("head2", $headers['head2']);
    }

    function testSetHeader()
    {
        $client = new HttpClient($this->url);
        $client->setHeader('head1', 'head1');
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $headers = array_change_key_case($json['HEADER'], CASE_LOWER);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("head1", $headers['head1']);
    }

    public function testHeaderValueCaseIsPreservedOverRealRequest(): void
    {
        $client = new HttpClient($this->url);
        $client->setHeader('X-Signature', 'AbC+/Z=');
        $json = json_decode($client->get()->getBody(), true);
        $headers = array_change_key_case($json['HEADER'], CASE_LOWER);

        $this->assertSame('AbC+/Z=', $headers['x-signature']);
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
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("cookie1", $json['COOKIE']['cookie1']);
        $this->assertEquals("cookie2", $json['COOKIE']['cookie2']);
    }

    function testAddCookie()
    {
        $client = new HttpClient($this->url);
        $client->addCookie('cookie1', 'cook');
        $response = $client->get(['head' => 'head']);
        $json = json_decode($response->getBody(), true);
        $headers = array_change_key_case($json['HEADER'], CASE_LOWER);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("GET", $json['REQUEST_METHOD']);
        $this->assertEquals("head", $headers['head']);
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
        $client->setHeader('aaa', 'bbb');
        $upgradeResult = $client->upgrade(true);
        $this->assertTrue($upgradeResult);
        $recvFrame = $client->recv();
        $this->assertEquals('bbb', json_decode($recvFrame->data, true)['aaa'] ?? '');

        $frame = new Frame();
        $frame->data = json_encode(['action' => 'hello', 'content' => ['a' => 1]]);
        $pushResult = $client->push($frame);
        $this->assertTrue($pushResult);

        $recvFrame = $client->recv();
        $this->assertIsBool(true, !!$recvFrame);
        $this->assertEquals('call hello with arg:{"a":1}', $recvFrame->data);
    }

    function testBasicAuth()
    {
        $httpClient = new HttpClient('127.0.0.1:9510');
        $httpClient->setBasicAuth('admin', '111111');
        $response = $httpClient->post();
        $res = $response->getBody();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $res);
    }

    function testFollowLocation()
    {
        $redirectUrl = 'http://127.0.0.1:9511/index.php?action=redirect&status=301'
            . '&location=' . rawurlencode('/index.php?action=final');
        $httpClient = new HttpClient($redirectUrl);
        $httpClient->enableFollowLocation(0);
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(301, $status);
        $httpClient = new HttpClient($redirectUrl);
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(200, $status);

        $httpClient->enableFollowLocation(0);
        $response = $httpClient->get();
        $status = $response->getStatusCode();
        $this->assertEquals(200, $status);
    }

    /**
     * @dataProvider redirectsThatSwitchPostToGet
     */
    public function testRedirectCanSwitchPostToGet(int $status): void
    {
        $url = 'http://127.0.0.1:9511/index.php?action=redirect&status=' . $status
            . '&location=' . rawurlencode('/index.php?redirected=1');
        $response = (new HttpClient($url))->postJson('{"value":1}');
        $json = json_decode($response->getBody(), true);
        $headers = array_change_key_case($json['HEADER'], CASE_LOWER);

        $this->assertSame('GET', $json['REQUEST_METHOD']);
        $this->assertSame('', $json['RAW']);
        $this->assertNotSame((string)strlen('{"value":1}'), $headers['content-length'] ?? null);
        $this->assertArrayNotHasKey('content-type', $headers);
    }

    public function redirectsThatSwitchPostToGet(): array
    {
        return [[301], [302], [303]];
    }

    /**
     * @dataProvider redirectsThatPreserveMethod
     */
    public function testRedirectCanPreservePostAndBody(int $status): void
    {
        $url = 'http://127.0.0.1:9511/index.php?action=redirect&status=' . $status
            . '&location=' . rawurlencode('/index.php?redirected=1');
        $response = (new HttpClient($url))->postJson('{"value":1}');
        $json = json_decode($response->getBody(), true);

        $this->assertSame('POST', $json['REQUEST_METHOD']);
        $this->assertSame('{"value":1}', $json['RAW']);
    }

    public function testQueryOnlyRedirectIsResolvedOverRealRequest(): void
    {
        $url = 'http://127.0.0.1:9511/index.php?action=redirect&status=302'
            . '&location=' . rawurlencode('?redirected=1');
        $json = json_decode((new HttpClient($url))->get()->getBody(), true);

        $this->assertSame(['redirected' => '1'], $json['GET']);
        $this->assertSame('/index.php?redirected=1', $json['REQUEST_URI']);
    }

    public function testCrossOriginRedirectDoesNotForwardCredentialsOverRealRequest(): void
    {
        $target = 'http://127.0.0.1:9512/index.php?redirected=1';
        $url = 'http://127.0.0.1:9511/index.php?action=redirect&status=302&location=' . rawurlencode($target);
        $client = new HttpClient($url);
        $client->setBasicAuth('User', 'Secret');
        $client->setHeader('Proxy-Authorization', 'ProxySecret', false);
        $client->addCookie('session', 'secret');

        $json = json_decode($client->get()->getBody(), true);
        $headers = array_change_key_case($json['HEADER'], CASE_LOWER);

        $this->assertArrayNotHasKey('authorization', $headers);
        $this->assertArrayNotHasKey('proxy-authorization', $headers);
        $this->assertSame([], $json['COOKIE']);
    }

    public function testRedirectLimitStopsALoop(): void
    {
        $client = new HttpClient('http://127.0.0.1:9511/index.php?action=redirect-loop');
        $client->setFollowLocation(2);

        $response = $client->get();
        $this->assertSame(302, $response->getStatusCode());
    }

    public function redirectsThatPreserveMethod(): array
    {
        return [[307], [308]];
    }

    public function testTemporaryRedirectReplaysUploadedFiles(): void
    {
        $url = 'http://127.0.0.1:9511/index.php?action=redirect&status=307'
            . '&location=' . rawurlencode('/index.php?redirected=1');
        $response = (new HttpClient($url))->post([
            'field' => 'value',
            'file' => new CURLFile(__FILE__, 'redirected-file'),
        ]);
        $json = json_decode($response->getBody(), true);

        $this->assertSame('POST', $json['REQUEST_METHOD']);
        $this->assertSame(['field' => 'value'], $json['POST']);
        $this->assertSame('HttpTest.php', $json['FILE']['redirected-file']['name']);
    }

    public function testGeneratedEntityHeadersDoNotLeakIntoNextRequest(): void
    {
        $client = new HttpClient('http://127.0.0.1:9511/index.php');
        $client->postJson('{"value":1}');
        $response = $client->get();
        $json = json_decode($response->getBody(), true);
        $headers = array_change_key_case($json['HEADER'], CASE_LOWER);

        $this->assertNotSame((string)strlen('{"value":1}'), $headers['content-length'] ?? null);
        $this->assertArrayNotHasKey('content-type', $headers);
        $this->assertSame('', $json['RAW']);
    }

    public function testStringZeroIsSentAsRequestBody(): void
    {
        $client = new HttpClient('http://127.0.0.1:9511/index.php');
        $json = json_decode($client->post('0')->getBody(), true);

        $this->assertSame('0', $json['RAW']);
    }

    public function testSetPath()
    {
        $httpClient = new HttpClient('http://127.0.0.1:9511/index.php?arg=1');
        $res = json_decode($httpClient->get()->getBody(), true);
        $this->assertSame('/index.php?arg=1', $res['REQUEST_URI']);

        $httpClient->setPath('/download.txt');
        $res = $httpClient->get()->getBody();
        $this->assertSame("http-client-download-fixture\n", $res);
    }

    public function testSetMethod()
    {
        $httpClient = new HttpClient('http://127.0.0.1:9511/index.php');
        $httpClient->setMethod('get');
        $this->assertEquals('get', $httpClient->getClient()->requestMethod);
        $httpClient->setMethod('post');
        $this->assertEquals('post', $httpClient->getClient()->requestMethod);
        $httpClient->setMethod('put');
        $this->assertEquals('put', $httpClient->getClient()->requestMethod);
        $httpClient->setMethod('delete');
        $this->assertEquals('delete', $httpClient->getClient()->requestMethod);
    }

    public function testHttpProxy()
    {
        $proxyHost = getenv('HTTP_CLIENT_HTTP_PROXY_HOST');
        $proxyPort = getenv('HTTP_CLIENT_HTTP_PROXY_PORT');
        if (!$proxyHost || !$proxyPort) {
            $this->markTestSkipped('Set HTTP_CLIENT_HTTP_PROXY_HOST and HTTP_CLIENT_HTTP_PROXY_PORT to run this test.');
        }

        $httpClient = new HttpClient(getenv('HTTP_CLIENT_PROXY_TARGET') ?: 'http://example.com');
        $httpClient->setProxyHttp($proxyHost, (int)$proxyPort);
        $response = $httpClient->get();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSocketProxy()
    {
        $proxyHost = getenv('HTTP_CLIENT_SOCKS5_PROXY_HOST');
        $proxyPort = getenv('HTTP_CLIENT_SOCKS5_PROXY_PORT');
        if (!$proxyHost || !$proxyPort) {
            $this->markTestSkipped('Set HTTP_CLIENT_SOCKS5_PROXY_HOST and HTTP_CLIENT_SOCKS5_PROXY_PORT to run this test.');
        }

        $httpClient = new HttpClient(getenv('HTTP_CLIENT_PROXY_TARGET') ?: 'http://example.com');
        $httpClient->setProxySocks5($proxyHost, (int)$proxyPort);
        $response = $httpClient->get();
        $this->assertEquals(200, $response->getStatusCode());
    }
}

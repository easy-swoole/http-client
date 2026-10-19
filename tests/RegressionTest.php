<?php

namespace EasySwoole\HttpClient\Test;

use EasySwoole\HttpClient\Bean\Response;
use EasySwoole\HttpClient\Bean\CURLFile;
use EasySwoole\HttpClient\Exception\InvalidUrl;
use EasySwoole\HttpClient\HttpClient;
use PHPUnit\Framework\TestCase;

class TestableHttpClient extends HttpClient
{
    public function resolveLocation(string $location): string
    {
        return $this->resolveRedirectUrl($location);
    }

    public function applyLocation(string $location): void
    {
        $this->applyRedirectLocation($location);
    }

    public function fullPath(): string
    {
        return $this->parserUrlInfo()->getFullPath();
    }

    public function redirectArguments(int $statusCode, string $method, $data = null, $contentType = null): array
    {
        return $this->redirectRequestArguments($statusCode, $method, $data, $contentType);
    }

    public function requestHeaders($data, $contentType = null, bool $stripEntityHeaders = false): array
    {
        return $this->prepareRequestHeaders($data, $contentType, $stripEntityHeaders);
    }
}

class RegressionTest extends TestCase
{
    public function testHeaderValueKeepsOriginalCase(): void
    {
        $client = new HttpClient('http://example.test');
        $client->setHeader('X-Signature', 'AbC+/Z=');

        $this->assertSame('AbC+/Z=', $client->getHeader()['x-signature']);
    }

    public function testSetQueryWorksWithoutAnExistingQuery(): void
    {
        $client = new TestableHttpClient('http://example.test/path');
        $client->setQuery(['a' => 1]);

        $this->assertSame('/path?a=1', $client->fullPath());
    }

    /**
     * @dataProvider invalidUrlProvider
     */
    public function testInvalidUrlAlwaysThrowsDomainException(string $url): void
    {
        $this->expectException(InvalidUrl::class);
        new HttpClient($url);
    }

    public function invalidUrlProvider(): array
    {
        return [
            'unparseable' => ['://'],
            'invalid host' => ['http://['],
            'invalid port' => ['http://example.test:99999'],
        ];
    }

    public function testRelativeRedirectResolution(): void
    {
        $client = new TestableHttpClient('https://example.test/a/b?old=1');

        $this->assertSame('https://example.test/new?x=1', $client->resolveLocation('/new?x=1'));
        $this->assertSame('https://example.test/next?x=1', $client->resolveLocation('../next?x=1'));
        $this->assertSame('https://example.test/a/b?page=2', $client->resolveLocation('?page=2'));
        $this->assertSame('https://other.test/path', $client->resolveLocation('//other.test/path'));
        $this->assertSame('https://example.test/a/b?old=1', $client->resolveLocation('#section'));
    }

    public function testCrossOriginRedirectRemovesCredentials(): void
    {
        $client = new TestableHttpClient('https://example.test/start');
        $client->setBasicAuth('User', 'Secret');
        $client->setHeader('Proxy-Authorization', 'ProxySecret', false);
        $client->setHeader('Cookie', 'manual=secret', false);
        $client->addCookie('session', 'secret');

        $client->applyLocation('/same-origin');
        $this->assertArrayHasKey('Authorization', $client->getHeader());
        $this->assertNotEmpty($client->getCookies());

        $client->applyLocation('https://other.test/target');
        $headers = array_change_key_case($client->getHeader(), CASE_LOWER);
        $this->assertArrayNotHasKey('authorization', $headers);
        $this->assertArrayNotHasKey('proxy-authorization', $headers);
        $this->assertArrayNotHasKey('cookie', $headers);
        $this->assertSame([], $client->getCookies());
    }

    public function testXmlParsingUsesSafeNonNetworkMode(): void
    {
        $response = new Response(['body' => '<root><name>value</name></root>']);
        $this->assertSame(['name' => 'value'], $response->xml(true));

        $invalid = new Response(['body' => '<root>']);
        $this->assertFalse($invalid->xml());
    }

    public function testRedirectMethodSemantics(): void
    {
        $client = new TestableHttpClient('https://example.test/start');

        $this->assertSame(
            [HttpClient::METHOD_GET, null, null, true],
            $client->redirectArguments(303, HttpClient::METHOD_PUT, 'body', 'text/plain')
        );
        $this->assertSame(
            [HttpClient::METHOD_GET, null, null, true],
            $client->redirectArguments(302, HttpClient::METHOD_POST, 'body', 'text/plain')
        );
        $this->assertSame(
            [HttpClient::METHOD_POST, 'body', 'text/plain', false],
            $client->redirectArguments(307, HttpClient::METHOD_POST, 'body', 'text/plain')
        );
        $this->assertSame(
            [HttpClient::METHOD_POST, 'body', 'text/plain', false],
            $client->redirectArguments(308, HttpClient::METHOD_POST, 'body', 'text/plain')
        );
    }

    public function testGeneratedEntityHeadersDoNotPollutePersistentHeaders(): void
    {
        $client = new TestableHttpClient('https://example.test/start');
        $headers = $client->requestHeaders('Body', 'Application/Json');

        $this->assertSame('4', $headers['content-length']);
        $this->assertSame('Application/Json', $headers['content-type']);
        $this->assertArrayNotHasKey('content-length', $client->getHeader());
        $this->assertArrayNotHasKey('content-type', $client->getHeader());
    }

    public function testEntityHeadersAreRemovedWhenRedirectChangesToGet(): void
    {
        $client = new TestableHttpClient('https://example.test/start');
        $client->setHeader('Content-Length', '12');
        $client->setHeader('Content-Type', 'text/plain');
        $client->setHeader('Transfer-Encoding', 'chunked');

        $headers = $client->requestHeaders(null, null, true);
        $this->assertArrayNotHasKey('content-length', $headers);
        $this->assertArrayNotHasKey('content-type', $headers);
        $this->assertArrayNotHasKey('transfer-encoding', $headers);
    }

    public function testCurlFileUsesNonDeprecatedUploadDefaults(): void
    {
        $file = new CURLFile(__FILE__, 'file');

        $this->assertSame(0, $file->getOffset());
        $this->assertSame(0, $file->getLength());
    }
}

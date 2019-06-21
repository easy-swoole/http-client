<?php


namespace EasySwoole\HttpClient\Test;


use EasySwoole\HttpClient\HttpClient;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    /*
     * url内容请看 tests/index.php
     */
    private $url = 'http://docker.local.com/index.php?arg1=1&arg2=2';

    function testGet()
    {
        $client = new HttpClient($this->url);
        $response = $client->get();
        $json = json_decode($response->getBody(),true);
        $this->assertEquals("GET",$json['REQUEST_METHOD']);
        $this->assertEquals([],$json['POST']);
        $this->assertEquals(['arg1'=>1,'arg2'=>2],$json['GET']);
    }

    function testOptions()
    {
        $client = new HttpClient($this->url);
        $response = $client->options(['op'=>'op1'],['head'=>'head']);
        $json = json_decode($response->getBody(),true);
        $this->assertEquals("OPTIONS",$json['REQUEST_METHOD']);
        $this->assertEquals("head",$json['HEADER']['head']);
    }

    function testPost()
    {
        $client = new HttpClient($this->url);
        $response = $client->post([
            'post1'=>'post1'
        ]);
        $json = json_decode($response->getBody(),true);
        $this->assertEquals("POST",$json['REQUEST_METHOD']);
        $this->assertEquals([ 'post1'=>'post1'],$json['POST']);
        $this->assertEquals(['arg1'=>1,'arg2'=>2],$json['GET']);
    }

    function testPostString()
    {
        $client = new HttpClient($this->url);
        $response = $client->post('postStr');
        $json = json_decode($response->getBody(),true);
        $this->assertEquals("POST",$json['REQUEST_METHOD']);
        $this->assertEquals([],$json['POST']);
        $this->assertEquals('postStr',$json['RAW']);
    }

    function testPostJson()
    {
        $client = new HttpClient($this->url);
        $response = $client->postJson(json_encode(['json'=>'json1']));
        $json = json_decode($response->getBody(),true);
        $this->assertEquals("POST",$json['REQUEST_METHOD']);
        $this->assertEquals([],$json['POST']);
        $this->assertEquals(['arg1'=>1,'arg2'=>2],$json['GET']);
        $raw = $json["RAW"];
        $raw = json_decode($raw,true);
        $this->assertEquals(['json'=>'json1'],$raw);
    }

    function testPostFile()
    {
        $client = new HttpClient($this->url);
        $response = $client->post([
            'post1'=>'post1',
            'file'=> new \CURLFile(__FILE__)
        ]);
        $json = json_decode($response->getBody(),true);
        $this->assertEquals("POST",$json['REQUEST_METHOD']);
        $this->assertEquals([ 'post1'=>'post1'],$json['POST']);
        $this->assertEquals(['arg1'=>1,'arg2'=>2],$json['GET']);
        $this->assertEquals('Test.php',$json['FILE']['file']['name']);
    }

    function testCookies()
    {
        $client = new HttpClient($this->url);
        $client->addCookies([
            'cookie1'=>'cook'
        ]);
        $response = $client->get(['head'=>'head']);
        $json = json_decode($response->getBody(),true);
        $this->assertEquals("GET",$json['REQUEST_METHOD']);
        $this->assertEquals("head",$json['HEADER']['head']);
        $this->assertEquals("cook",$json['COOKIE']['cookie1']);
    }
}
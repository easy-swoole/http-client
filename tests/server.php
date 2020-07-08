<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */

Co\run(function () {
    $server = new \Swoole\Coroutine\Http\Server('0.0.0.0', 9510);
    $server->handle('/json/', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
        $array = [
            'title' => 'easyswoole',
            'desc' => 'swoole framework'
        ];
        $response->end(json_encode($array));
    });

    $server->handle('/jsonp/', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
        $array = [
            'title' => 'easyswoole',
            'desc' => 'swoole framework'
        ];
        $json = json_encode($array);
        $response->end("callback({$json})");
    });

    $server->handle('/xml/', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml .= "<test>\n";
        $xml .= "<title>easyswoole</title>\n";
        $xml .= "<desc>swoole framework</desc>\n";
        $xml .= "</test>\n";
        $response->header('Content-Type', 'text/xml');
        $response->end($xml);
    });

    $server->start();
});
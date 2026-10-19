<?php

$httpClientTestProcesses = [];

$isListening = static function (int $port): bool {
    $socket = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.1);
    if (is_resource($socket)) {
        fclose($socket);
        return true;
    }
    return false;
};

$startServer = static function (array $command, int $port) use (&$httpClientTestProcesses, $isListening): void {
    if ($isListening($port)) {
        return;
    }

    $log = fopen(sys_get_temp_dir() . "/http-client-test-{$port}.log", 'ab');
    $pipes = [];
    $process = proc_open($command, [['pipe', 'r'], $log, $log], $pipes, dirname(__DIR__));
    if (!is_resource($process)) {
        throw new RuntimeException("Unable to start test server on port {$port}");
    }
    fclose($pipes[0]);
    fclose($log);
    $httpClientTestProcesses[] = $process;

    $deadline = microtime(true) + 3;
    while (!$isListening($port) && microtime(true) < $deadline) {
        usleep(20000);
    }
    if (!$isListening($port)) {
        throw new RuntimeException("Test server on port {$port} did not become ready");
    }
};

$startServer([PHP_BINARY, __DIR__ . '/server/server.php'], 9510);
$startServer([PHP_BINARY, '-S', '127.0.0.1:9511', '-t', __DIR__ . '/server'], 9511);
$startServer([PHP_BINARY, '-S', '127.0.0.1:9512', '-t', __DIR__ . '/server'], 9512);

register_shutdown_function(static function () use (&$httpClientTestProcesses): void {
    foreach ($httpClientTestProcesses as $process) {
        proc_terminate($process);
        proc_close($process);
    }
});

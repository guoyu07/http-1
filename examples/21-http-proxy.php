<?php

use React\EventLoop\Factory;
use React\Socket\Server;
use React\Http\Response;
use Psr\Http\Message\RequestInterface;
use RingCentral\Psr7;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();
$socket = new Server(isset($argv[1]) ? $argv[1] : '0.0.0.0:0', $loop);

$server = new \React\Http\Server($socket, function (RequestInterface $request) {
    if (strpos($request->getRequestTarget(), '://') === false) {
        return new Response(
            400,
            array('Content-Type' => 'text/plain'),
            'This is a plain HTTP proxy'
        );
    }

    // prepare outgoing client request by updating request-target and Host header
    $host = (string)$request->getUri()->withScheme('')->withPath('')->withQuery('');
    $target = (string)$request->getUri()->withScheme('')->withHost('')->withPort(null);
    if ($target === '') {
        $target = $request->getMethod() === 'OPTIONS' ? '*' : '/';
    }
    $outgoing = $request->withRequestTarget($target)->withHeader('Host', $host);

    // pseudo code only: simply dump the outgoing request as a string
    // left up as an exercise: use an HTTP client to send the outgoing request
    // and forward the incoming response to the original client request
    return new Response(
        200,
        array('Content-Type' => 'text/plain'),
        Psr7\str($outgoing)
    );
});

//$server->on('error', 'printf');

echo 'Listening on http://' . $socket->getAddress() . PHP_EOL;

$loop->run();

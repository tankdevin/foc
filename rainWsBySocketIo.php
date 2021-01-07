<?php

use Workerman\Worker;
use PHPSocketIO\SocketIO;

// tp大坑 不引入base会因为验证码报错
// PHP Fatal error:  Uncaught Error: Class 'Route' not found in /Users/espira/code/backend/php/noahsark/vendor/topthink/think-captcha/src/helper.php:12
require __DIR__ . '/thinkphp/base.php';
require_once __DIR__ . '/vendor/autoload.php';

if (!preg_match("/cli/i", php_sapi_name())) {
    die("the run mode must be cli");
}
// SSL context
$context = array(
    'ssl' => array(
        'local_cert'  => '/www/server/panel/vhost/cert/backend.noahsark.top/fullchain.pem',
        'local_pk'    => '/www/server/panel/vhost/cert/backend.noahsark.top/privkey.pem',
        'verify_peer' => false
    )
);
$io = new SocketIO(2120);
// 监听一个http端口，用来推送数据
// eg: http://ip:9191?event=kline&msg=xxxx
$io->on('workerStart', function () use ($io) {
    $inner_http_worker = new Worker('http://0.0.0.0:5678');
    $inner_http_worker->onMessage = function ($http_connection, $request) use ($io) {
        $data = $request->post();
        if ($request->method() != "POST") {
            return $http_connection->send('fail, request method not allow');
        }
        if (!isset($data['msg'])) {
            return $http_connection->send('fail, $_GET["msg"] not found');
        }
        if (!isset($data['event'])) {
            return $http_connection->send('fail, $_GET["event"] not found');
        }
        $allowEvent = ['kline','daymarket','transaction'];
        if (!in_array($data['event'], $allowEvent)) {
            return $http_connection->send('fail, $_GET["event"] not allow');
        }
        $msg = [];
        try {
            $msg = json_decode($data['msg']);
        } catch (Exception $e) {
            $msg = $msg;
        }
        $io->emit($data['event'], $msg);
        $http_connection->send('ok');
    };
    $inner_http_worker->listen();
});
$io->on('connection', function ($socket) {
    $socket->addedUser = false;

    // handle ping message
    $socket->on('ping', function ($data) use ($socket) {
        $socket->emit('pong');
    });

    // When the client emits 'add user', this listens and executes
    $socket->on('login', function ($username) use ($socket) {
        global $usernames, $numUsers;

        // We store the username in the socket session for this client
        $socket->username = $username;
        // Add the client's username to the global list
        $usernames[$username] = $username;
        ++$numUsers;

        $socket->addedUser = true;
        $socket->emit('login:success', [
            'username' => $username
        ]);
    });

    // When the user disconnects, perform this
    $socket->on('disconnect', function () use ($socket) {
        global $usernames, $numUsers;

        // Remove the username from global usernames list
        if ($socket->addedUser) {
            unset($usernames[$socket->username]);
            --$numUsers;

            // echo globally that this client has left
            $socket->broadcast->emit('user left', array(
                'username' => $socket->username,
                'numUsers' => $numUsers
            ));
        }
    });
});

Worker::runAll();
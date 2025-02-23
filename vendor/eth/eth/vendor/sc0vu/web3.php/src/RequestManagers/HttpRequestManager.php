<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Web3\RequestManagers;

use InvalidArgumentException;
use RuntimeException as RPCException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use Web3\RequestManagers\RequestManager;
use Web3\RequestManagers\IRequestManager;

class HttpRequestManager extends RequestManager implements IRequestManager
{
    /**
     * client
     * 
     * @var \GuzzleHttp
     */
    protected $client;

    /**
     * construct
     * 
     * @param string $host
     * @param int $timeout
     * @return void
     */
    public function __construct($host, $timeout = 30)
    {
        parent::__construct($host, $timeout);
        $this->client = new Client;
    }

    /**
     * sendPayload
     * 
     * @param string $payload
     * @param callable $callback
     * @return void
     */
    public function sendPayload($payload, $callback)
    {
        if (!is_string($payload)) {
            throw new \InvalidArgumentException('Payload must be string.');
        }
        // $promise = $this->client->postAsync($this->host, [
        //     'headers' => [
        //         'content-type' => 'application/json'
        //     ],
        //     'body' => $payload
        // ]);
        // $promise->then(
        //     function (ResponseInterface $res) use ($callback) {
        //         var_dump($res->body());
        //         call_user_func($callback, null, $res);
        //     },
        //     function (RequestException $err) use ($callback) {
        //         var_dump($err->getMessage());
        //         call_user_func($callback, $err, null);
        //     }
        // );
        try {
            $res = $this->client->post($this->host, [
                'headers' => [
                    'content-type' => 'application/json'
                ],
                'body' => $payload,
                'timeout' => $this->timeout,
                'connect_timeout' => $this->timeout
            ]);
            $json = json_decode($res->getBody());

            if (JSON_ERROR_NONE !== json_last_error()) {
                call_user_func($callback, new InvalidArgumentException('json_decode error: ' . json_last_error_msg()), null);
            }
            if (is_array($json)) {
                // batch results
                $results = [];
                $errors = [];

                foreach ($json as $result) {
                    if (isset($result->result)) {
                        $results[] = $result->result;
                    } else {
                        if (isset($json->error)) {
                            $error = $json->error;
                            $errors[] = new RPCException(mb_ereg_replace('Error: ', '', $error->message), $error->code);
                        } else {
                            $results[] = null;
                        }
                    }
                }
                if (count($errors) > 0) {
                    call_user_func($callback, $errors, $results);
                } else {
                    call_user_func($callback, null, $results);
                }
            } elseif (isset($json->result)) {
                call_user_func($callback, null, $json->result);
            } else {
                if (isset($json->error)) {
                    $error = $json->error;

                    call_user_func($callback, new RPCException(mb_ereg_replace('Error: ', '', $error->message), $error->code), null);
                } else {
                    call_user_func($callback, new RPCException('Something wrong happened.'), null);
                }
            }
        } catch (RequestException $err) {
            call_user_func($callback, $err, null);
        }
    }
}

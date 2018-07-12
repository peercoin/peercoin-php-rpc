<?php
require_once '../vendor/autoload.php';

use Peercoin\RpcClient;

try {
    $client = new RpcClient("localhost");
    $client->auth("peercoinrpc", "4sQWxWJdFcg3wNXm5kLAW5CXGRr9nsZQEaaGZd2pDhVH");

    // if you call more that one method batch request will perform
    $response = $client->getInfo()->getBlockCount()->execute();
    var_dump($response);
} catch (\Peercoin\Exceptions\RpcException $e) {
    var_dump($e->getMessage());
}
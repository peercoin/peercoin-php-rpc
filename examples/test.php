<?php
require_once '../vendor/autoload.php';

use Peercoin\RpcClient;

$client = new RpcClient("localhost");
$client->init("peercoinrpc", "4sQWxWJdFcg3wNXm5kLAW5CXGRr9nsZQEaaGZd2pDhVH");

try {
    // if you call more that one method batch request will perform
    $response = $client->getInfo()->getBlockCount()->execute();
} catch (\Peercoin\Exceptions\RpcException $e) {
    var_dump($e->getMessage());
}

var_dump($response);
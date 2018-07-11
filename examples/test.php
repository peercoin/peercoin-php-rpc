<?php
require_once '../vendor/autoload.php';

use Peercoin\RpcClient;

$client = new RpcClient("localhost");
$client->init("peercoinrpc", "4sQWxWJdFcg3wNXm5kLAW5CXGRr9nsZQEaaGZd2pDhVH");

$response = $client->getInfo();

var_dump($response);
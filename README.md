# peercoin-php-rpc

peercoin-php-rpc is a simple and minimal library made for communication with `peercoind` via JSON-RPC protocol for PHP 7.1+. Easiest way to use is to use composer. Otherwise include `RpcClient` class in your project an you are good to go.

## How to use

Here in an example on how to use this lib:

```php
$client = new RpcClient("localhost");
$client->auth("peercoinrpc", "4sQWxWJdFcg3wNXm5kLAW5CXGRr9nsZQEaaGZd2pDhVH");

try {
    $response = $client->getInfo()->getBlockCount()->execute();
} catch (\Peercoin\Exceptions\RpcException $e) {
    var_dump($e->getMessage());
}
```

Lib automatically performs a batch request if more than one method is in the chain. Response array will return responses in order in which methods are called.


## Docker

This lib includes docker environment with all extensions installed so you can try it. In order to use it run:
```bash
docker build -t peercoin/php-rpc -f .docker/Dockerfile .
docker-compose up -d

## after docker in up and running open the container go to /opt/examples and run test
docker exec -it peercoin_rpc /bin/bash
cd /opt/examples
php test.php
```


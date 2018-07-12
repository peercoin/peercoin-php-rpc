<?php
namespace Peercoin;

/**
 * @method RpcClient getInfo()
 *
 * @method RpcClient walletPassphrase($passphrase, $timeout = 99999999, $mintOnly = true)
 *
 * @method RpcClient getBlock($blockHash)
 * @method RpcClient getBlockCount()
 * @method RpcClient getBlockHash($index)
 *
 * @method RpcClient getTransaction($transactionId)
 * @method RpcClient getBalance($account = "", $minConf = 6)
 * @method RpcClient getReceivedByAddress($account = "", $minConf = 1)
 *
 * @method RpcClient getDifficulty()
 * @method RpcClient getPeerInfo()
 *
 * @method RpcClient getAddressesByAccount($account = "")
 * @method RpcClient getNewAddress($label = "")
 * @method RpcClient getAccount($address = "")
 * @method RpcClient getAccountAddress($account)
 * @method RpcClient sendToAddress($recvAddr, $amount, $comment = "")
 * @method RpcClient sendFrom($account, $address, $amount)
 * @method RpcClient sendMany($recvDict, $account = "", $comment = "")
 *
 * @method RpcClient getConnectionCount()
 *
 * @method RpcClient getRawTransaction($transactionId, $verbose = 0)
 * @method RpcClient getRawMempool()
 *
 * @method RpcClient listTransactions($account = "", $many = 999, $since = 0)
 * @method RpcClient listReceivedByAddress($minConf = 0, $includeEmpty = true)
 * @method RpcClient listReceivedByAccount($minConf = 0, $includeEmpty = true)
 * @method RpcClient listAccounts($minConf = 1)
 * @method RpcClient listUnspent($minConf = 1, $maxConf = 999999)
 *
 * @method RpcClient dumpPrivKey($address)
 * @method RpcClient importPrivKey($wif, $accountName = "")
 *
 * @method RpcClient createRawTransaction($inputs, $outputs)
 * @method RpcClient decodeRawTransaction($transactionHash)
 * @method RpcClient signRawTransaction($rawTransactionHash)
 * @method RpcClient sendRawTransaction($signedRawTransactionHash)
 *
 * @method RpcClient validateAddress($address)
 *
 * @method RpcClient signMessage($address, string $message)
 * @method RpcClient verifyMessage($address, $signature, $message)
 *
 * @method RpcClient encryptWallet($passPhrase)
 */

class RpcClient
{
    /** @var string $endpoint */
    private $endpoint = "";

    /** @var string $endpoint */
    private $host;

    /** @var int $port */
    private $port;

    /** @var array $request */
    private $requests;

    function __construct(
        string $host,
        ?int $port = null,
        bool $isTestnet = false
    ) {
        $this->host = $host;
        $this->port = $port ? $port : $isTestnet ? 9904 : 9902;
    }

    /**
     * @param string $username
     * @param string $password
     * @return void
     * @throws Exceptions\RpcException
     */
    public function auth(string $username, string $password): void
    {
        if ($username && $password) {
            $this->buildEndpoint($username, $password);
            return;
        }

        throw new Exceptions\RpcException('Username and/or password must not be empty');
    }

    /**
     * @param string $path
     * @return void
     * @throws Exceptions\RpcException
     */
    public function authFromFile(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exceptions\RpcException('File on given path does not exist');
        }
        $data = file_get_contents($path);

        preg_match('/rpcuser=(.+)/', $data, $username);
        preg_match('/rpcpassword=(.+)/', $data, $password);

        if (isset($username[1]) && isset($password[1])) {
            $this->buildEndpoint($username[1], $password[1]);
            return;
        }

        throw new Exceptions\RpcException('Username and/or password must not be empty');
    }

    /**
     * Perform a JSON-RPC request and returns the result
     *
     * @param string $name
     * @param array $arguments
     * @return RpcClient
     * @throws Exceptions\RpcException
     */
    public function __call(string $name, array $arguments): RpcClient
    {
        $this->requests[] = array(
            'method' => strtolower($name),
            'params' => $arguments
        );

        return $this;
    }

    /**
     * Perform a JSON-RPC batch requests and returns the results for each request
     *
     * @return array
     * @throws Exceptions\RpcException
     */
    public function execute(): array 
    {
        $reqNo = count($this->requests);

        if ($reqNo == 0) {
            throw new Exceptions\RpcException('You need to have at least one request to perform execute');
        }

        if($reqNo > 1) {
            // there are more requests to execute to call batch execute
            return $this->batch();
        }

        $this->requests = $this->requests[0];
        $this->requests['jsonrpc'] = '1.1';

        return $this->request();
    }

    /**
     * Perform a JSON-RPC batch requests and returns the results for each request
     *
     * @return array
     * @throws Exceptions\RpcException
     */
    private function batch(): array
    {
        foreach ($this->requests as $id => &$request) {
            $request['jsonrpc'] = '2.0';
            $request['id'] = $id;
        }

        return $this->request();
    }

    /**
     * @return array
     * @throws Exceptions\RpcException
     */
    private function request(): array
    {
        if (empty($this->endpoint)) {
            throw new Exceptions\RpcException('Use init(username = null, password = null) method to set credentials.');
        }

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode($this->requests)
            )
        );

        $ctx = stream_context_create($options);
        if ($fp = fopen($this->endpoint, 'r', false, $ctx)) {
            $response = '';
            while ($row = fgets($fp)) {
                $response .= trim($row) . "\n";
            }
        } else {
            throw new Exceptions\RpcException('Connection to given endpoint failed.');
        }

        return json_decode($response, true);
    }

    /**
     * @param string $username
     * @param string $password
     * @return void
     */
    private function buildEndpoint(string $username, string $password): void
    {
        $this->endpoint = sprintf("http://%s:%s@%s:%s/",
            $username,
            $password,
            $this->host,
            $this->port
        );
    }
}
<?php
namespace Peercoin;

/**
 * @method getInfo()
 *
 * @method walletPassphrase($passphrase, $timeout = 99999999, $mintOnly = true)
 *
 * @method getBlock($blockHash)
 * @method getBlockCount()
 * @method getBlockHash($index)
 *
 * @method getTransaction($transactionId)
 * @method getBalance($account = "", $minConf = 6)
 * @method getReceivedByAddress($account = "", $minConf = 1)
 *
 * @method getDifficulty()
 * @method getPeerInfo()
 *
 * @method getAddressesByAccount($account = "")
 * @method getNewAddress($label = "")
 * @method getAccount($address = "")
 * @method getAccountAddress($account)
 * @method sendToAddress($recvAddr, $amount, $comment = "")
 * @method sendFrom($account, $address, $amount)
 * @method sendMany($recvDict, $account = "", $comment = "")
 *
 * @method getConnectionCount()
 *
 * @method getRawTransaction($transactionId, $verbose = 0)
 * @method getRawMempool()
 *
 * @method listTransactions($account = "", $many = 999, $since = 0)
 * @method listReceivedByAddress($minConf = 0, $includeEmpty = true)
 * @method listReceivedByAccount($minConf = 0, $includeEmpty = true)
 * @method listAccounts($minConf = 1)
 * @method listUnspent($minConf = 1, $maxConf = 999999)
 *
 * @method dumpPrivKey($address)
 * @method importPrivKey($wif, $accountName = "")
 *
 * @method createRawTransaction($inputs, $outputs)
 * @method decodeRawTransaction($transactionHash)
 * @method signRawTransaction($rawTransactionHash)
 * @method sendRawTransaction($signedRawTransactionHash)
 *
 * @method validateAddress($address)
 *
 * @method signMessage($address, string $message)
 * @method verifyMessage($address, $signature, $message)
 *
 * @method encryptWallet($passPhrase)
 */

class RpcClient
{
    /** @var string $endpoint */
    private $endpoint = "";

    /** @var string $endpoint */
    private $host;

    /** @var int $port */
    private $port;

    function __construct(
        string $host,
        ?int $port = null,
        bool $isTestnet = false
    ) {
        $this->host = $host;
        $this->port = $port ? $port : $isTestnet ? 9904 : 9902;
    }

    public function init(?string $username = null, ?string $password = null)
    {
        // TODO: load from file or throw an exception if credentials are unavailable
        $this->endpoint = sprintf("http://%s:%s@%s:%s/",
            $username,
            $password,
            $this->host,
            $this->port
        );
    }

    /**
     * Perform a JSON-RPC request and returns the result
     *
     * @param string $name
     * @param array $arguments
     * @return array
     * @throws Exceptions\RpcException
     */
    public function __call(string $name, array $arguments): array
    {
        $request = array(
            'method' => strtolower($name),
            'params' => $arguments,
            'jsonrpc' => '1.1'
        );

        return $this->request($request);
    }


    /**
     * @param array $request
     * @return array
     * @throws Exceptions\RpcException
     */
    private function request(array $request): array
    {
        if (empty($this->endpoint)) {
            throw new Exceptions\RpcException('Use init(username = null, password = null) method to set credentials.');
        }

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode($request)
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
}
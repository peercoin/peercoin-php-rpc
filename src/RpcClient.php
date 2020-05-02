<?php
namespace Peercoin;

/**
 * @method RpcClient getInfo()
 * @method RpcClient getBlockCount()
 * @method RpcClient getNewAddress($account, $addressType)
 * @method RpcClient getAccountAddress($account)
 * @method RpcClient getRawChangeAddress($addressType)
 * @method RpcClient setAccount($address, $account)
 * @method RpcClient getAccount($address)
 * @method RpcClient getAddressesByAccount($account)
 * @method RpcClient sendToAddress($address, $amount, $comment, $commentTo, $subtractfeefromamount, $replaceable, $confTarget)
 * @method RpcClient listAddressGroupings()
 * @method RpcClient signMessage($address, $message)
 * @method RpcClient getReceivedByAddress($account, $minConf = 1)
 * @method RpcClient getReceivedByAccount($account, $minConf = 1)
 * @method RpcClient getBalance($account, $minConf = 1, $includeWatchonly = false)
 * @method RpcClient getUnconfirmedBalance()
 * @method RpcClient move($fromaccount, $toaccount, $amount, $minconf = 1, $comment = "")
 * @method RpcClient sendFrom($fromaccount, $toaccount, $amount, $minconf = 1, $comment = "", $commentTo = "")
 * @method RpcClient sendMany($fromaccount, $amounts, $minconf = 1, $comment = "", $subtractfeefrom, $replaceable, $confTarget)
 * @method RpcClient addMultiSigAddress($nrequired, $keys, $account, $addressType)
 * @method RpcClient addWitnessAddress($address, $p2sh)
 * @method RpcClient listReceivedByAddress($minConf = 1, $includeEmpty = false, $includeWatchonly = false)
 * @method RpcClient listReceivedByAccount($minConf = 1, $includeEmpty = false, $includeWatchonly = false)
 * @method RpcClient listTransactions($account, $count = 10, $skip = 0, $includeWatchonly = false)
 * @method RpcClient listAccounts($minConf = 1, $includeWatchonly = false)
 * @method RpcClient listSinceBlock()
 * @method RpcClient getTransaction($transactionId, $includeWatchonly = false)
 * @method RpcClient abandonTransaction($transactionID)
 * @method RpcClient backupWallet($destination)
 * @method RpcClient keyPoolRefill($newsize)
 * @method RpcClient walletPassphrase($passphrase, $timeout)
 * @method RpcClient walletPassphraseChange($oldpassphrase, $newpassphrase)
 * @method RpcClient walletLock()
 * @method RpcClient encryptWallet($passPhrase)
 * @method RpcClient lockUnspent($unlock, $transactions)
 * @method RpcClient listLockUnspent()
 * @method RpcClient getWalletInfo()
 * @method RpcClient listWallets()
 * @method RpcClient resendWalletTransactions()
 * @method RpcClient listUnspent($minConf = 1, $maxConf = 999999, $addresses, $include_unsafe, $query_options)
 * @method RpcClient fundRawtTransaction($hexstring, $options, $isWitness)
 * @method RpcClient generate($nblocks, $maxtries)
 * @method RpcClient rescanBlockchain($startHeight, $stopHeight)
 * @method RpcClient listMinting($count, $from)
 * @method RpcClient makeKeyPair($prefix)
 * @method RpcClient showKeyPair($hexprivkey)
 * @method RpcClient reserveBalance($reserve, $amount)
 * @method RpcClient abortRescan()
 * @method RpcClient dumpPrivKey($address)
 * @method RpcClient importPrivKey($privkey, $label, $rescan = true)
 * @method RpcClient importAddress($address, $label, $rescan, $p2sh)
 * @method RpcClient importPubKey($pubkey, $label, $rescan)
 * @method RpcClient dumpWallet($filename)
 * @method RpcClient importWallet($filename)
 * @method RpcClient importMulti($requests, $options)
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

        // flush requests to avoid accumulation sequential calls
        $this->requests = [];

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
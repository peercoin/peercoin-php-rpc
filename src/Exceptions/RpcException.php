<?php
namespace Peercoin\Exceptions;

class RpcException extends \Exception
{
    public function __construct($message = "Internal Server Error", $code = 500, \Exception $previous = null)
    {
        // make sure everything is assigned properly
        parent::__construct("[Peercoin RPC Exception]: " . $message, $code, $previous);
    }
}
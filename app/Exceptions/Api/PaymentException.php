<?php

namespace App\Exceptions\Api;

use Exception;

class PaymentException extends Exception
{
    private $res;
    private $sessionId;
    private $paymentId;

    public function __construct($res, string $sessionId, string $paymentId)
    {
        $this->res = $res;
        $this->sessionId = $sessionId;
        $this->paymentId = $paymentId;
    }

    public function getResponse()
    {
        return $this->res;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function getPaymentId()
    {
        return $this->paymentId;
    }
}

<?php

namespace App\Support;

use App\Exceptions\Api\JsonException;
use App\Exceptions\Api\PaymentException;
use App\Support\Facades\Neonomics;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;

class ApiHelper
{
    private $httpClient;
    private $lastFailedRequest;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Send a new http request.
     *
     * @return object
     */
    public function request(string $token, string $method, string $uri, array $data = [])
    {
        if ($token) $data['headers']['authorization'] = "Bearer {$token}";

        try {
            $res = $this->httpClient->request($method, $uri, $data);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            $this->lastFailedRequest = [$method, $uri, $data];
            $errorCode = $e->getResponse()->getStatusCode();

            if ($errorCode == 510) {
                return $this->clientErrors($e);
            }
            if ($errorCode == 520) {
                return $this->neonomicsErrors($e);
            }
            if ($errorCode == 530) {
                return $this->bankErrors($e);
            }

            throw new JsonException(502);
        } catch (GuzzleException $e) {
            throw new JsonException(500);
        }
    }

    private function clientErrors($e)
    {
        $body = $this->getErrorBody($e);

        // invalid payment id state
        if ($body->errorCode === "1009") {
            throw new JsonException(409, 'Resource ID is in invalid state, payment already completed.');
        }
        // consent missing
        if ($body->errorCode === "1426") {
            $this->attemptApprovalFix($body);
        }
        // payment authorization
        if ($body->errorCode === "1428") {
            $isPayment = true;
            $this->attemptApprovalFix($body, $isPayment);
        }
        // bad request
        if ($body->errorCode < 2000) {
            throw new JsonException(400, $body->message);
        }
        // invalid or expired access token
        if ($body->errorCode === "2001" || $body->errorCode === "2002") {
            $token = Neonomics::updateTokens();
            return $this->runLastFailedRequest($token);
        }
        // forbidden
        if ($body->errorCode === "2004") {
            throw new JsonException(403, $body->message);
        }
        // invalid client id/secret
        if ($body->errorCode === "2005") {
            throw new JsonException(401, $body->message);
        }
        // expired refresh token
        if ($body->errorCode === "2009") {
            $token = Neonomics::updateTokens();
            return $this->runLastFailedRequest($token);
        }
        // others
        throw new JsonException(400, "Error-$body->errorCode from Neonomics. $body->message");
    }

    private function neonomicsErrors($e)
    {
        $body = $this->getErrorBody($e);

        // network error
        if ($body->errorCode === "3901") {
            throw new JsonException(408, 'Network error, please retry');
        }
        // others
        throw new JsonException(503, "Error-{$body->errorCode} from Neonomics, please contact us.");
    }

    private function bankErrors($e)
    {
        $body = $this->getErrorBody($e);

        // x-psu-id is required by the bank
        if ($body->errorCode === "5001") {
            throw new JsonException(400, 'x-identification-id is required.');
        }
        // others
        throw new JsonException(503, "Error-{$body->errorCode} from selected bank, please contact us.");
    }

    private function attemptApprovalFix($body, bool $payment = false)
    {
        $link = $body->links[0];
        $url = $link->href;
        $data = $this->lastFailedRequest[2];
        $res = Neonomics::getApproval($url, $data);

        if ($payment) {
            $sessionId = $this->lastFailedRequest[2]['headers']['x-session-id'];
            $paymentId = $link->meta->id;
            throw new PaymentException($res, $sessionId, $paymentId);
        }

        throw new JsonException(401, 'Approval URL', $res);
    }

    private function getErrorBody($e)
    {
        $body = json_decode($e->getResponse()->getBody());
        if (!is_object($body)) throw new JsonException(400);

        // no error code provded
        if (!property_exists($body, 'errorCode')) {
            if (property_exists($body, 'message')) {
                throw new JsonException(503, $body->message);
            }
            throw new JsonException(503, 'Unknown error, please contact us.');
        }

        return $body;
    }

    private function runLastFailedRequest(string $token = '')
    {
        return $this->request($token, ...$this->lastFailedRequest);
    }
}

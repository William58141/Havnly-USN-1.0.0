<?php

namespace App\Support;

use App\Exceptions\Api\JsonException;
use App\Exceptions\Api\PaymentException;
use App\Models\User;
use App\Models\Bank;
use App\Models\Session;
use App\Models\Account;
use App\Models\Payment;
use stdClass;

class Client
{
    private $apiHelper;
    private $user;

    public function __construct(ApiHelper $apiHelper)
    {
        $this->apiHelper = $apiHelper;
        $this->user = auth()->user();
    }

    //------//
    // Auth //
    //------//

    /**
     * Get new tokens.
     *
     * @return object
     */
    public function getTokens(string $clientId, string $clientSecret)
    {
        $data = [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'scope' => 'openid',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]
        ];
        return $this->apiHelper->request('', 'POST', env('NEONOMICS_AUTH_URL'), $data);
    }

    /**
     * Get new tokens and update database.
     *
     * @return string new access token
     */
    public function updateTokens()
    {
        $user = User::where('client_id', $this->user->client_id)->first();
        $res = $this->getTokens($user->client_id, $user->client_secret);
        $user->access_token = $res->access_token;
        $user->refresh_token = $res->refresh_token;
        $user->save();
        return $res->access_token;
    }

    //-------------------------//
    // Consent & Authorization //
    //-------------------------//

    /**
     * Get approval URL for end user.
     *
     * @return array
     */
    public function getApproval(string $url, array $data)
    {
        $data['headers']['x-redirect-url'] = $this->user->redirect_url;
        $res = $this->apiHelper->request($this->user->access_token, 'GET', $url, $data);
        $link = $res->links[0];
        return [
            'type' => $link->type,
            'rel' => $link->rel,
            'href' => $link->href,
        ];
    }

    //------//
    // Bank //
    //------//

    public function getBanks(string $params)
    {
        $uri = 'banks';
        if ($params) $uri = $uri . '?' . $params;
        $data['headers']['x-device-id'] = 'neonomics';
        $res = $this->apiHelper->request($this->user->access_token, 'GET', $uri, $data);
        return Bank::jsonDeserialize($res);
    }

    public function getBankByID(string $id)
    {
        $url = "banks/{$id}";
        $data['headers']['x-device-id'] = 'neonomics';
        $res = $this->apiHelper->request($this->user->access_token, 'GET', $url, $data);
        return Bank::jsonDeserialize($res);
    }

    //---------//
    // Session //
    //---------//

    private function getOrCreateSession(string $deviceId, string $bankId)
    {
        $session = Session::where('user_id', $this->user->id)
            ->where('device_id', $deviceId)
            ->where('bank_id', $bankId)
            ->first();
        if (!$session) {
            $session = $this->createSession($deviceId, $bankId);
        }
        return $session;
    }

    private function createSession(string $deviceId, string $bankId)
    {
        $data = [
            'headers' => ['x-device-id' => $deviceId],
            'json' => ['bankId' => $bankId]
        ];
        $res = $this->apiHelper->request($this->user->access_token, 'POST', 'session', $data);
        $session = Session::create([
            'user_id' => $this->user->id,
            'device_id' => $deviceId,
            'bank_id' => $bankId,
            'session_id' => $res->sessionId,
        ]);
        return $session;
    }

    //---------//
    // Account //
    //---------//

    public function getAccounts(string $deviceId, string $bankId, string $personalNumber)
    {
        $url = 'accounts';
        $res = $this->baseAccountRequest($url, $deviceId, $bankId, $personalNumber);
        return Account::jsonDeserialize($res);
    }

    public function getAccountByID(string $id, string $deviceId, string $bankId, string $personalNumber)
    {
        $url = "accounts/{$id}";
        $res = $this->baseAccountRequest($url, $deviceId, $bankId, $personalNumber);
        return Account::jsonDeserialize($res);
    }

    public function getAccountBalancesByID(string $id, string $deviceId, string $bankId, string $personalNumber)
    {
        $url = "accounts/{$id}/balances";
        return $this->baseAccountRequest($url, $deviceId, $bankId, $personalNumber);
    }

    public function getAccountTransactionsByID(string $id, string $deviceId, string $bankId, string $personalNumber)
    {
        $url = "accounts/{$id}/transactions";
        return $this->baseAccountRequest($url, $deviceId, $bankId, $personalNumber);
    }

    private function baseAccountRequest(string $url, string $deviceId, string $bankId, string $personalNumber)
    {
        $data = $this->getAccountRequestData($deviceId, $bankId, $personalNumber);
        return $this->apiHelper->request($this->user->access_token, 'GET', $url, $data);
    }

    private function getAccountRequestData(string $deviceId, string $bankId, string $personalNumber)
    {
        $session = $this->getOrCreateSession($deviceId, $bankId);
        $data['headers'] = [
            'x-device-id' => $deviceId,
            'x-session-id' => $session->session_id,
            'x-psu-ip-address' => request()->ip(),
            'x-psu-id' => $this->encryptIdentifier($this->user->encryption_key, $personalNumber),
        ];
        return $data;
    }

    //---------//
    // Payment //
    //---------//

    public function newPayment(string $deviceId, string $bankId, string $personalNumber, object $json)
    {
        $url = 'payments/domestic-transfer';
        $data = $this->getPaymentRequestData($deviceId, $bankId, $personalNumber, $json);
        try {
            $this->apiHelper->request($this->user->access_token, 'POST', $url, $data);
            return [
                'device_id' => $deviceId,
                'action' => 'Payment authorized.',
            ];
        } catch (PaymentException $e) {
            $sessionId = $e->getSessionId();
            $paymentId = $e->getPaymentId();
            $this->createPayment($sessionId, $paymentId);
            throw new JsonException(401, 'Approval URL', $e->getResponse());
        }
    }

    public function completePayment(string $id, string $deviceId, string $sessionId)
    {
        $url = "payments/domestic-transfer/{$id}/complete";
        $data = [
            'headers' => [
                'x-device-id' => $deviceId,
                'x-session-id' => $sessionId,
                'x-psu-ip-address' => request()->ip(),
            ],
            'json' => [],
        ];
        $this->apiHelper->request($this->user->access_token, 'POST', $url, $data);
        return true;
    }

    private function getPaymentRequestData(string $deviceId, string $bankId, string $personalNumber, object $json)
    {
        $session = $this->getOrCreateSession($deviceId, $bankId);
        $data = [
            'headers' => [
                'x-device-id' => $deviceId,
                'x-session-id' => $session->session_id,
                'x-redirect-url' => $this->user->redirect_url,
                'x-psu-ip-address' => request()->ip(),
                'x-psu-id' => $this->encryptIdentifier($this->user->encryption_key, $personalNumber),
            ],
            'json' => [
                'debtorAccount' => ['bban' => $json->debtor_account->bban],
                'creditorAccount' => ['bban' => $json->creditor_account->bban],
                'debtorName' => $json->debtor_account->owner,
                'creditorName' => $json->creditor_account->owner,
                'currency' => strtoupper($json->currency),
                'instrumentedAmount' => $json->amount,
                'endToEndIdentification' => $json->identification,
                'remittanceInformationUnstructured' => property_exists($json, 'note') ? $json->note : '',
                'paymentMetadata' => new stdClass(), // => {}
            ],
        ];
        return $data;
    }

    private function createPayment(string $sessionId, string $paymentId)
    {
        $payment = Payment::create([
            'user_id' => $this->user->id,
            'session_id' => $sessionId,
            'payment_id' => $paymentId,
        ]);
        return $payment;
    }

    //---------//
    // Helpers //
    //---------//

    private function encryptIdentifier(string $encryptionKey, string $personalNumber)
    {
        if ($personalNumber) {
            $data_to_encrypt = $personalNumber;
            $cipher = "aes-128-gcm";
            $raw_data = $encryptionKey;
            $key = base64_decode($raw_data);
            if (in_array($cipher, openssl_get_cipher_methods())) {
                $iv_len = openssl_cipher_iv_length($cipher);
                $iv = openssl_random_pseudo_bytes($iv_len);
                $tag = "";
                $ciphertext = openssl_encrypt($data_to_encrypt, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
                $with_iv = base64_encode($iv . $ciphertext . $tag);
                return $with_iv;
            }
            throw new JsonException(500, 'Invalid encryption cipher, please contact us.');
        }
        return '';
    }
}

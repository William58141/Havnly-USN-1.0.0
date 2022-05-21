<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Api\JsonException;
use App\Http\Controllers\Controller;
use App\Support\Facades\Neonomics;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    private $deviceId;
    private $bankId;
    private $personalNumber;

    public function __construct(Request $request)
    {
        $this->deviceId = $request->header('x-device-id');
        $this->bankId = $request->header('x-bank-id');
        $this->personalNumber = $request->header('x-identification-id', '');

        if (!$this->deviceId || !$this->bankId) {
            throw new JsonException(400, 'x-device-id and x-bank-id is required.');
        }
    }

    public function index()
    {
        $res = Neonomics::getAccounts($this->deviceId, $this->bankId, $this->personalNumber);
        return $this->responseJson($res);
    }

    public function show(string $id)
    {
        $res = Neonomics::getAccountByID($id, $this->deviceId, $this->bankId, $this->personalNumber);
        return $this->responseJson($res);
    }

    public function showBalances(string $id)
    {
        $res = Neonomics::getAccountBalancesByID($id, $this->deviceId, $this->bankId, $this->personalNumber);
        return $this->responseJson($res);
    }

    public function showTransactions(string $id)
    {
        $res = Neonomics::getAccountTransactionsByID($id, $this->deviceId, $this->bankId, $this->personalNumber);
        return $this->responseJson($res);
    }
}

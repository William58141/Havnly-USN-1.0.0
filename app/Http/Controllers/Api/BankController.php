<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Facades\Neonomics;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $params = $this->getQueryParams($request);
        $res = Neonomics::getBanks($params);
        return $this->responseJson($res);
    }

    public function show(string $id)
    {
        $res = Neonomics::getBankByID($id);
        return $this->responseJson($res);
    }

    // HELPER METHODS

    private function getQueryParams(Request $request)
    {
        $params = '';
        if ($request->has('name')) {
            $params = 'name=' . $request->query('name');
        } else if ($request->has('countryCode')) {
            $params = 'countryCode=' . $request->query('countryCode');
        }
        return $params;
    }
}

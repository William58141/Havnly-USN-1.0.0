<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Api\JsonException;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Session;
use App\Support\Facades\Neonomics;

class ResourceController extends Controller
{
    public function show(string $id)
    {
        $payment = $this->getPayment($id);

        if ($payment) {
            $session = $this->getOrFailSession($payment->session_id);
            Neonomics::completePayment($id, $session->device_id, $session->session_id);
            $deviceId = $session->device_id;
            $action = 'Payment authorized';
            $payment->delete();
        } else {
            $session = $this->getOrFailSession($id);
            $deviceId = $session->device_id;
            $action = 'Consent approved';
        }

        return $this->responseJson([
            'device_id' => $deviceId,
            'action' => $action,
        ]);
    }

    private function getOrFailSession(string $id)
    {
        $session = Session::where('user_id', auth()->user()->id)
            ->where('session_id', $id)
            ->first();
        if (!$session) {
            throw new JsonException(400, 'Invalid resource ID.');
        }
        return $session;
    }

    private function getPayment(string $id)
    {
        return Payment::where('user_id', auth()->user()->id)
            ->where('payment_id', $id)
            ->first();
    }
}

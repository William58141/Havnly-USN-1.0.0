<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Api\JsonException;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Support\Facades\Neonomics;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $status = 200;

    public function index(Request $request)
    {
        $user = $this->getUser($request) ?? $this->createUser($request);

        $user->tokens()->delete();
        $authToken = $user->createToken($user->name)->plainTextToken;

        return $this->responseJson(['access_token' => $authToken], $this->status);
    }

    public function update(Request $request)
    {
        $user = $this->getUser($request);

        if (!$user) {
            throw new JsonException(401);
        }

        $this->updateUser($request, $user);
        return $this->responseJson($user, $this->status);
    }

    // HELPER METHODS

    private function getUser(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
        ]);

        $user = User::where('client_id', $request->client_id)
            ->where('client_secret', $request->client_secret)
            ->first();

        return $user;
    }

    private function createUser(Request $request)
    {
        $this->status = 201;
        $request->validate([
            'name' => ['required', 'string'],
            'client_id' => ['required', 'string', 'unique:users,client_id'],
            'client_secret' => ['required', 'string'],
            'encryption_key' => ['required', 'string'],
            'redirect_url' => ['required', 'string'],
        ]);

        $tokens = Neonomics::getTokens($request->client_id, $request->client_secret);

        $user = User::create([
            'name' => $request->name,
            'client_id' => $request->client_id,
            'client_secret' => $request->client_secret,
            'encryption_key' => $request->encryption_key,
            'redirect_url' => $request->redirect_url,
            'access_token' => $tokens->access_token,
            'refresh_token' => $tokens->refresh_token,
        ]);

        return $user;
    }

    private function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'encryption_key' => ['required', 'string'],
            'redirect_url' => ['required', 'string'],
        ]);

        $user->name = $request->name;
        $user->encryption_key = $request->encryption_key;
        $user->redirect_url = $request->redirect_url;

        return $user->save();
    }
}

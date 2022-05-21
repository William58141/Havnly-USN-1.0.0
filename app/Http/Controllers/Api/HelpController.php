<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class HelpController extends Controller
{
    public function index()
    {
        return $this->responseJson([
            'Authentication' => [
                [
                    'uri' => '/token',
                    'method' => 'POST',
                    'json' => [
                        'name' => 'Your application name. (Required for first time user)',
                        'client_id' => 'Client_id from Neonomics.',
                        'client_secret' => 'Client_secret from Neonomics.',
                        'encryption_key' => 'Value of the rawValue field from the Neonomics encryption key. (Required for first time user)',
                        'redirect_url' => 'Callback after user consent and payment authentication. (Required for first time user)',
                    ],
                    'description' => 'Used to create new user or authenticate and get access token.',
                ],
                [
                    'uri' => '/token',
                    'method' => 'PUT',
                    'json' => [
                        'name' => 'Your application name.',
                        'client_id' => 'Client_id from Neonomics.',
                        'client_secret' => 'Client_secret from Neonomics.',
                        'encryption_key' => 'Value of the rawValue field from the Neonomics encryption key.',
                        'redirect_url' => 'Callback after user consent and payment authentication.',
                    ],
                    'description' => 'Used to update existing user.',
                ],
            ],
            'Banks' => [
                [
                    'uri' => '/banks',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>'
                    ],
                    'description' => 'Get all available banks.',
                ],
                [
                    'uri' => '/banks?countryCode={value}',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>'
                    ],
                    'description' => 'Get all banks in a given country.',
                ],
                [
                    'uri' => '/banks?name={value}',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>.'
                    ],
                    'description' => 'Get bank by it\'s name.',
                ],
                [
                    'uri' => '/banks/{id}',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>'
                    ],
                    'description' => 'Get bank by it\'s ID.',
                ],
            ],
            'Accounts' => [
                [
                    'uri' => '/accounts',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>',
                        'x-device-id' => 'Your applications user ID.',
                        'x-bank-id' => 'One of the provided bank ID\'s.',
                        'x-identification-id' => 'A users social security number. (Required if bank has requireIdentification=true)'
                    ],
                    'description' => 'Get all accounts for a user.',
                ],
                [
                    'uri' => '/accounts/{id}',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>',
                        'x-device-id' => 'Your applications user ID.',
                        'x-bank-id' => 'One of the provided bank ID\'s.',
                        'x-identification-id' => 'A users social security number. (Required if bank has requireIdentification=true)'
                    ],
                    'description' => 'Get account by it\'s ID.',
                ],
                [
                    'uri' => '/accounts/{id}/transactions',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>',
                        'x-device-id' => 'Your applications user ID.',
                        'x-bank-id' => 'One of the provided bank ID\'s.',
                        'x-identification-id' => 'A users social security number. (Required if bank has requireIdentification=true)'
                    ],
                    'description' => 'Get all transactions for an account.',
                ],
                [
                    'uri' => '/accounts/{id}/balances',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>',
                        'x-device-id' => 'Your applications user ID.',
                        'x-bank-id' => 'One of the provided bank ID\'s.',
                        'x-identification-id' => 'A users social security number. (Required if bank has requireIdentification=true)'
                    ],
                    'description' => 'Get all balances for an account.',
                ],
            ],
            'Payments' => [
                [
                    'uri' => '/payments',
                    'method' => 'POST',
                    'headers' => [
                        'authorization' => 'Bearer <token>',
                        'x-device-id' => 'Your applications user ID.',
                        'x-bank-id' => 'One of the provided bank ID\'s.',
                        'x-identification-id' => 'A users social security number. (Required if bank has requireIdentification=true)'
                    ],
                    'json' => [
                        'debtor_account' => [
                            'owner' => 'The name of the account owner.',
                            'bban' => 'The accounts bban number.',
                        ],
                        'creditor_account' => [
                            'owner' => 'The name of the account owner.',
                            'bban' => 'The accounts bban number.',
                        ],
                        'currency' => '3 letter currency code.',
                        'amount' => 'Monetary amount to be transferred. (Precision from 0 - 2 decimal places)',
                        'identification' => 'Unique reference/id assigned by the debtor, max 35 characters.',
                        'note' => 'OPTIONAL - Max 140 characters. (Exceptions: Swedbank - 10, Other banks in Sweden - 12, Banks in Denmark - 40)',
                    ],
                    'description' => 'Create a new domestic payment',
                ]
            ],
            'Other' => [
                [
                    'uri' => '/resources/{id}',
                    'method' => 'GET',
                    'headers' => [
                        'authorization' => 'Bearer <token>'
                    ],
                    'description' => 'Get user and action related to the redirect_url resource ID.',
                ],
                [
                    'uri' => '/help',
                    'method' => 'GET',
                    'description' => 'Shows a list of all available endpoints, with required data.',
                ],
            ],
        ]);
    }
}

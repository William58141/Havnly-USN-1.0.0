<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    public string $id;
    public string $bban;
    public string $iban;
    public string $accountName;
    public string $accountType; // not in use, not supported by all banks
    public string $ownerName;
    public string $displayName;
    public array $balances;

    public function __construct(string $id, string $bban, string $iban, string $accountName, string $ownerName, string $displayName, array $balances)
    {
        $this->id = $id;
        $this->bban = $bban;
        $this->iban = $iban;
        $this->accountName = $accountName;
        $this->ownerName = $ownerName;
        $this->displayName = $displayName;
        $this->balances = $balances;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'bban' => $this->bban,
            'owner' => $this->ownerName,
            'name' => $this->displayName,
            'balances' => $this->balances,
        ];
    }

    public static function jsonDeserialize($json)
    {
        if (is_array($json)) {
            $accounts = [];
            foreach ($json as $account) {
                $newAccount = new Account($account->id, $account->bban, $account->iban, $account->accountName, $account->ownerName, $account->displayName, $account->balances);
                array_push($accounts, $newAccount);
            }
            return $accounts;
        }
        return new Account($json->id, $json->bban, $json->iban, $json->accountName, $json->ownerName, $json->displayName, $json->balances);
    }
}

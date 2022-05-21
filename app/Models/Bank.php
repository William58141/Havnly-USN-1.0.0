<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    public string $countryCode;
    public string $bankingGroupName;
    public bool $personalIdentificationRequired;
    public string $id;
    public string $bankDisplayName;
    public array $supportedServices;
    public string $bic;
    public string $bankOfficialName;
    public string $status;

    public function __construct(string $countryCode, string $bankingGroupName, bool $personalIdentificationRequired, string $id, string $bankDisplayName, array $supportedServices, string $bic, string $bankOfficialName, $status)
    {
        $this->countryCode = $countryCode;
        $this->bankingGroupName = $bankingGroupName;
        $this->personalIdentificationRequired = $personalIdentificationRequired;
        $this->id = $id;
        $this->bankDisplayName = $bankDisplayName;
        $this->supportedServices = $supportedServices;
        $this->bic = $bic;
        $this->bankOfficialName = $bankOfficialName;
        $this->status = $status;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'countryCode' => $this->countryCode,
            'name' => $this->bankDisplayName,
            'requireIdentification' => $this->personalIdentificationRequired,
        ];
    }

    public static function jsonDeserialize($json)
    {
        if (is_array($json)) {
            $banks = [];
            foreach ($json as $bank) {
                $newBank = new Bank($bank->countryCode, $bank->bankingGroupName, $bank->personalIdentificationRequired, $bank->id, $bank->bankDisplayName, $bank->supportedServices, $bank->bic, $bank->bankOfficialName, $bank->status);
                array_push($banks, $newBank);
            }
            return $banks;
        }
        return new Bank($json->countryCode, $json->bankingGroupName, $json->personalIdentificationRequired, $json->id, $json->bankDisplayName, $json->supportedServices, $json->bic, $json->bankOfficialName, $json->status);
    }
}

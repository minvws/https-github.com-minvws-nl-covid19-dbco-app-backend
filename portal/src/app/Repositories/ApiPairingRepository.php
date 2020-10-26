<?php

namespace App\Repositories;

class ApiPairingRepository implements PairingRepository
{
    /**
     * @param $caseUuid The case to pair
     * @return string A pairingcode for this case
     */
    public function getPairingCode(string $caseUuid): string
    {
        // Todo: call pairing API
        return "123456789012";
    }
}

<?php

namespace App\Repositories;

interface PairingRepository
{
    /**
     * @param $caseUuid The case to pair
     * @return string A pairingcode for this case
     */
    public function getPairingCode(string $caseUuid): string;
}

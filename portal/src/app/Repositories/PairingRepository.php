<?php

namespace App\Repositories;

use App\Models\Pairing;
use DateTimeInterface;

interface PairingRepository
{
    /**
     * Fetch pairing code for the given case.
     *
     * @param string            $caseUuid  The case to pair.
     * @param DateTimeInterface $expiresAt When it is not possible anymore to submit data for this case.
     *
     * @return Pairing A pairing code for this case
     */
    public function getPairing(string $caseUuid, DateTimeInterface $expiresAt): Pairing;
}

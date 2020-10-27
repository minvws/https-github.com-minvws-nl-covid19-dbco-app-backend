<?php

namespace App\Repositories;

use DateTimeInterface;

interface PairingRepository
{
    /**
     * Fetch pairing code for the given case.
     *
     * @param string            $caseUuid  The case to pair.
     * @param DateTimeInterface $expiresAt When it is not possible anymore to submit data for this case.
     *
     * @return string A pairing code for this case
     */
    public function getPairingCode(string $caseUuid, DateTimeInterface $expiresAt): string;
}

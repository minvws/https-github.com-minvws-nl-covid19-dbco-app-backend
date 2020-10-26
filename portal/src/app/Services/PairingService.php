<?php

namespace App\Services;

use App\Repositories\PairingRepository;

class PairingService
{
    private PairingRepository $pairingRepository;

    public function __construct(PairingRepository $pairingRepository)
    {
        $this->pairingRepository = $pairingRepository;
    }

    public function getPairingCodeForCase(string $caseUuid): string
    {
        $code = $this->pairingRepository->getPairingCode($caseUuid);

        // apply formatting for readability.
        return implode('-', str_split($code, 3));
    }
}

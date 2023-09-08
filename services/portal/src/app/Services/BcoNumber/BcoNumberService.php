<?php

declare(strict_types=1);

namespace App\Services\BcoNumber;

use App\Models\Eloquent\BcoNumber;
use Illuminate\Database\QueryException;

class BcoNumberService
{
    private BcoNumberGenerator $bcoCodeGenerator;
    private int $maxBcoNumberRetries;

    public function __construct(int $maxRetries, BcoNumberGenerator $bcoCodeGenerator)
    {
        $this->maxBcoNumberRetries = $maxRetries;
        $this->bcoCodeGenerator = $bcoCodeGenerator;
    }

    public function makeUniqueNumber(): BcoNumber
    {
        for ($i = 0; $i < $this->maxBcoNumberRetries; $i++) {
            $bcoNumber = $this->generateNumber();
            if ($bcoNumber !== null) {
                return $bcoNumber;
            }
        }

        throw new BcoNumberException('Could not make unique bco number after ' . $i . ' tries.');
    }

    private function generateNumber(): ?BcoNumber
    {
        try {
            return BcoNumber::create(['bco_number' => $this->buildCode()]);
        } catch (QueryException $e) {
            return null;
        }
    }

    protected function buildCode(): string
    {
        return $this->bcoCodeGenerator->buildCode();
    }
}

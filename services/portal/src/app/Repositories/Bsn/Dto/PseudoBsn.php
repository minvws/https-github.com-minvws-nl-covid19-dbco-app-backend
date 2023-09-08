<?php

declare(strict_types=1);

namespace App\Repositories\Bsn\Dto;

class PseudoBsn
{
    private string $guid;
    private string $censoredBsn;
    private string $letters;
    private ?string $exchangeToken;

    public function __construct(
        string $guid,
        string $censoredBsn,
        string $letters,
        ?string $exchangeToken = null,
    ) {
        $this->guid = $guid;
        $this->censoredBsn = $censoredBsn;
        $this->letters = $letters;
        $this->exchangeToken = $exchangeToken;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getCensoredBsn(): string
    {
        return $this->censoredBsn;
    }

    public function getLetters(): string
    {
        return $this->letters;
    }

    public function getExchangeToken(): ?string
    {
        return $this->exchangeToken;
    }

    public function toArray(): array
    {
        return [
            'guid' => $this->getGuid(),
            'censoredBsn' => $this->getCensoredBsn(),
            'letters' => $this->getLetters(),
        ];
    }
}

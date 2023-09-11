<?php

declare(strict_types=1);

namespace App\Models\ValueObjects;

use function ctype_digit;
use function strpos;

class CaseIdentifier
{
    private string $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function isBcoPortalNumber(): bool
    {
        return strpos($this->identifier, '-') !== false;
    }

    public function isMonsterNumber(): bool
    {
        return !$this->isBcoPortalNumber() && !$this->isHpzoneNumber();
    }

    public function isHpzoneNumber(): bool
    {
        return ctype_digit($this->identifier);
    }
}

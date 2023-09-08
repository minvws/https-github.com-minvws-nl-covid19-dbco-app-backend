<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

interface IdentifiableEnum
{
    public function getIdentifier(): string;

    public function getLabel(): string;

    /**
     * @return array<static>
     */
    public static function cases(): array;

    public static function from(string $identifier): static;

    public static function tryFrom(string $identifier): ?static;
}

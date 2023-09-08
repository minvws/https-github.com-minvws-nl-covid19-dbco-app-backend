<?php

declare(strict_types=1);

namespace App\Models\Intake;

use DateTimeInterface;

class RawIntake
{
    public function __construct(
        private readonly string $id,
        private readonly string $type,
        private readonly string $source,
        private readonly array $identityData,
        private readonly array $intakeData,
        private readonly ?array $handoverData,
        private readonly DateTimeInterface $receivedAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getIdentityData(): array
    {
        return $this->identityData;
    }

    public function getIntakeData(): array
    {
        return $this->intakeData;
    }

    public function getHandoverData(): ?array
    {
        return $this->handoverData;
    }

    public function getReceivedAt(): DateTimeInterface
    {
        return $this->receivedAt;
    }
}

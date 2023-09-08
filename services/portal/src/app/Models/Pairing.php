<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

class Pairing
{
    public ?string $code;
    public DateTimeImmutable $expiresAt;
}

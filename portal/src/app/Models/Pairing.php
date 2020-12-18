<?php

namespace App\Models;

use DateTimeImmutable;

class Pairing
{
    public string $code;
    public DateTimeImmutable $expiresAt;
}

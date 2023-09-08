<?php

declare(strict_types=1);

namespace App\Dto\Osiris\Client;

final class Credentials
{
    public function __construct(
        public readonly string $sysLogin,
        public readonly string $sysPassword,
        public readonly string $userLogin,
    ) {
    }
}

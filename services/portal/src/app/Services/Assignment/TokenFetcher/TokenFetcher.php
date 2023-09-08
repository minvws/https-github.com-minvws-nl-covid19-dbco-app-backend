<?php

declare(strict_types=1);

namespace App\Services\Assignment\TokenFetcher;

interface TokenFetcher
{
    public function hasToken(): bool;

    public function getToken(): string;
}

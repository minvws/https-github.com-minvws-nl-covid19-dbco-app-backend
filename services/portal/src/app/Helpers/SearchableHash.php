<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTimeInterface;
use Propaganistas\LaravelPhone\PhoneNumber;
use Throwable;

use function config;
use function hash;
use function sprintf;
use function strtolower;
use function trim;

class SearchableHash
{
    public function hash(string $string): string
    {
        $salt = config('security.indexSalt');

        return hash('sha256', sprintf('%s#%s', $salt, strtolower($string)));
    }

    public function hashForLastNameAndDateOfBirth(string $lastname, DateTimeInterface $dateOfBirth): string
    {
        return $this->hash(sprintf('%s#%s', trim(strtolower($lastname)), $dateOfBirth->format('Y-m-d')));
    }

    public function hashForLastNameAndEmail(string $lastname, string $email): string
    {
        return $this->hash(sprintf('%s#%s', trim(strtolower($lastname)), trim(strtolower($email))));
    }

    public function hashForLastNameAndPhone(string $lastname, string $phone): ?string
    {
        try {
            $phoneNumber = new PhoneNumber($phone, 'NL');

            return $this->hash(sprintf('%s#%s', trim(strtolower($lastname)), $phoneNumber->formatE164()));
        } catch (Throwable $exception) {
            // Invalid phone number, do nothing
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

class ContactDetailsAnswer extends Answer
{
    public ?string $firstname;
    public ?string $lastname;
    public ?string $email;
    public ?string $phonenumber;

    public const FIELD_FIRSTNAME = 'firstname';
    public const FIELD_LASTNAME = 'lastname';
    public const FIELD_PHONENUMBER = 'phonenumber';
    public const FIELD_EMAIL = 'email';

    public static function getValidationRules(): array
    {
        return [
            self::FIELD_FIRSTNAME => 'nullable|string',
            self::FIELD_LASTNAME => 'nullable|string',
            self::FIELD_PHONENUMBER => 'nullable|string',
            self::FIELD_EMAIL => 'nullable|email',
        ];
    }

    public function toFormValue(): array
    {
        return [
            self::FIELD_FIRSTNAME => $this->firstname,
            self::FIELD_LASTNAME => $this->lastname,
            self::FIELD_EMAIL => $this->email,
            self::FIELD_PHONENUMBER => $this->phonenumber,
        ];
    }

    public function fromFormValue(array $formData): void
    {
        $this->firstname = $formData[self::FIELD_FIRSTNAME] ?? null;
        $this->lastname = $formData[self::FIELD_LASTNAME] ?? null;
        $this->email = $formData[self::FIELD_EMAIL] ?? null;
        $this->phonenumber = $formData[self::FIELD_PHONENUMBER] ?? null;
    }
}

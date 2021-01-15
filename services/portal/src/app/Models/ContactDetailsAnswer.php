<?php

namespace App\Models;

class ContactDetailsAnswer extends Answer
{
    public ?string $firstname;
    public ?string $lastname;
    public ?string $email;
    public ?string $phonenumber;

    public static function getValidationRules()
    {
        return [
            'firstName' => 'string',
            'lastName' => 'string',
            'phoneNumber' => 'string',
            'email' => 'email'
        ];
    }

    public function isCompleted(): bool
    {
        return
            !empty($this->firstname) &&
            !empty($this->lastname) &&
            !empty($this->email) &&
            !empty($this->phonenumber);
    }

    public function toFormValue()
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phonenumber' => $this->phonenumber
        ];
    }

    public function fromFormValue(array $formData)
    {
        $this->firstname = $formData['firstname'] ?? null;
        $this->lastname = $formData['lastname'] ?? null;
        $this->email = $formData['email'] ?? null;
        $this->phonenumber = $formData['phonenumber'] ?? null;
    }
}

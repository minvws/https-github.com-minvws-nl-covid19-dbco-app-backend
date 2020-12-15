<?php

namespace App\Models;

class ContactDetailsAnswer extends Answer
{
    public ?string $firstname;
    public ?string $lastname;
    public ?string $email;
    public ?string $phonenumber;

    public function progressContribution(): bool
    {
        return
            !empty($this->firstname) &&
            !empty($this->lastname) &&
            (!empty($this->email) || !empty($this->phonenumber));
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
}

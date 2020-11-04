<?php

namespace App\Models;

class ContactDetailsAnswer extends Answer
{
    public ?string $firstname;
    public ?string $lastname;
    public ?string $email;
    public ?string $phonenumber;

    function progressContribution()
    {
        $progress = 0;
        if (!empty($this->firstname)) {
            $progress += 25;
        }
        if (!empty($this->lastname)) {
            $progress += 25;
        }
        if (!empty($this->email) || !empty($this->phonenumber)) {
            $progress += 25;
        }
        return $progress;
    }
}

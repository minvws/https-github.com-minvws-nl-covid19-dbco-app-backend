<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;

class BCOUser
{
    public string $uuid;
    public string $name;
    public string $externalId;

    public array $organisations = [];
    public array $roles = [];

}

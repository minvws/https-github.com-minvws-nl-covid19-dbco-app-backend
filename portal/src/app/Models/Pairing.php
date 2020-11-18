<?php

namespace App\Models;

use Jenssegers\Date\Date;

class Pairing
{
    public string $code;
    public Date $expiresAt;
}

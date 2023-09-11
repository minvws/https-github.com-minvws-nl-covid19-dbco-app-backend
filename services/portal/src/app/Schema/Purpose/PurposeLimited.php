<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

interface PurposeLimited
{
    public function getPurposeLimitation(): PurposeLimitation;
}

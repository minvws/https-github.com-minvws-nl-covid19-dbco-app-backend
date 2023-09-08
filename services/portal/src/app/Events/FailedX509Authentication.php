<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FailedX509Authentication
{
    use Dispatchable;
    use SerializesModels;
}

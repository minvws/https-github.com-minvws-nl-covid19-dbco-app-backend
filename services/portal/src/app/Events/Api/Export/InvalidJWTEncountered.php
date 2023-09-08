<?php

declare(strict_types=1);

namespace App\Events\Api\Export;

use App\Models\Export\ExportClient;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvalidJWTEncountered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ExportClient $client,
    ) {
    }
}

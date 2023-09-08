<?php

declare(strict_types=1);

namespace App\Services\SecureMail;

use Illuminate\Support\Facades\Http;

class SecureMailV2Client extends SecureMailBaseClient
{
    public function __construct(
        string $baseUrl,
        string $apiToken,
    ) {
        $this->http = Http::baseUrl($baseUrl)
            ->withToken($apiToken)
            ->asJson()
            ->acceptJson();
    }
}

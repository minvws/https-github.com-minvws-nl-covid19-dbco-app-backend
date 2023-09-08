<?php

declare(strict_types=1);

namespace App\Services\SecureMail;

use Carbon\CarbonImmutable;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class SecureMailV1Client extends SecureMailBaseClient
{
    public function __construct(
        string $baseUrl,
        string $jwtSecret,
    ) {
        $jwtToken = JWT::encode([
            'iat' => CarbonImmutable::now()->timestamp,
            'exp' => CarbonImmutable::now()->addSeconds(60)->timestamp,
        ], $jwtSecret, 'HS256', 'portal');

        $this->http = Http::baseUrl($baseUrl)->withToken($jwtToken);
    }
}

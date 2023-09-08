<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

use function is_null;

final class Token implements Arrayable
{
    /**
     * @param string $iss The "issuer" claim. The service that issued the JWT token.
     * @param string $aud The "audience" claim. Recipient for which the JWT is intended.
     * @param string $sub The "subject" claim. The user for which the JWT is intended.
     * @param int $exp The "expiration" claim. Time after which the JWT expires.
     * @param int $iat The "issued at time" claim. Time at which the JWT was issued
     * @param ?string $jti The "JWT ID" claim. Unique identifier of the token.
     * @param Collection<int,TokenResource> $res The resources.
     */
    public function __construct(
        public readonly string $iss,
        public readonly string $aud,
        public readonly string $sub,
        public readonly int $exp,
        public readonly int $iat,
        public readonly ?string $jti,
        public readonly Collection $res,
    ) {
    }

    public function toArray(): array
    {
        $result = [
            'iss' => $this->iss,
            'aud' => $this->aud,
            'sub' => $this->sub,
            'exp' => $this->exp,
            'iat' => $this->iat,
            'res' => $this->res->toArray(),
        ];

        if (!is_null($this->jti)) {
            $result['jti'] = $this->jti;
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

use function preg_match;

final class JwtTokenService
{
    private string $jwtSecret;

    public function __construct(string $jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
    }

    /**
     * @throws Exception
     */
    public function getJwtPayloadFromRequest(Request $request): array
    {
        if (!$request->headers->has('Authorization')) {
            throw new Exception('Authorization token not found');
        }

        /** @var string $header */
        $header = $request->headers->get('Authorization');
        if (!preg_match('/Bearer\s(?P<token>\S+)/', $header, $matches)) {
            throw new Exception('Invalid authorization token');
        }

        try {
            return (array) JWT::decode($matches['token'], new Key($this->jwtSecret, 'HS256'));
        } catch (Exception $exception) {
            throw new Exception('Unable to decode authorization token', $exception->getCode(), $exception);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Assignment\TokenEncoder;

use App\Services\Assignment\Exception\AssignmentDomainException;
use App\Services\Assignment\Exception\AssignmentInvalidArgumentException;
use App\Services\Assignment\Exception\AssignmentInvalidValueException;
use App\Services\Assignment\Token;
use DomainException;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Config\Repository as Config;
use InvalidArgumentException;

use function assert;
use function is_string;
use function sprintf;

/**
 * @implements TokenEncoder<Token>
 */
final class FirebaseHs256TokenEncoder implements TokenEncoder
{
    private string $jwtKey;

    public function __construct(Config $config)
    {
        $jwtKey = $config->get('assignment.jwt.secret');

        assert(is_string($jwtKey), AssignmentInvalidValueException::wrongType('jwtKey', 'string', $jwtKey));

        $this->jwtKey = $jwtKey;
    }

    /**
     * @param Token $payload
     */
    public function encode(object $payload): string
    {
        assert(
            $payload instanceof Token,
            new AssignmentInvalidArgumentException(
                sprintf('Expected payload of type "%s", got "%s"', Token::class, $payload::class),
            ),
        );

        try {
            return $this->doEncode($payload->toArray());
        } catch (DomainException $e) {
            throw new AssignmentDomainException($e->getMessage(), $e->getCode(), $e);
        } catch (InvalidArgumentException $e) {
            throw new AssignmentInvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function doEncode(array $payload): string
    {
        return JWT::encode($payload, $this->jwtKey, 'HS256');
    }
}

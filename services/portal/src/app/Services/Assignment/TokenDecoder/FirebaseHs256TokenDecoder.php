<?php

declare(strict_types=1);

namespace App\Services\Assignment\TokenDecoder;

use App\Services\Assignment\Exception\AssignmentBeforeValidException;
use App\Services\Assignment\Exception\AssignmentDomainException;
use App\Services\Assignment\Exception\AssignmentExpiredException;
use App\Services\Assignment\Exception\AssignmentInvalidValueException;
use App\Services\Assignment\Exception\AssignmentSignatureInvalidException;
use App\Services\Assignment\Exception\AssignmentUnexpectedValueException;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Contracts\Config\Repository as Config;
use stdClass;
use UnexpectedValueException;

use function assert;
use function is_string;

/**
 * @implements TokenDecoder<stdClass>
 */
final class FirebaseHs256TokenDecoder implements TokenDecoder
{
    private string $jwtKey;

    public function __construct(Config $config)
    {
        $jwtKey = $config->get('assignment.jwt.secret');

        assert(is_string($jwtKey), AssignmentInvalidValueException::wrongType('jwtKey', 'string', $jwtKey));

        $this->jwtKey = $jwtKey;
    }

    public function decode(string $token): object
    {
        try {
            return $this->doDecode($token);
        } catch (SignatureInvalidException $e) {
            // Provided JWT was invalid because the signature verification failed
            throw new AssignmentSignatureInvalidException($e->getMessage(), $e->getCode(), $e);
        } catch (BeforeValidException $e) {
            // Provided JWT is trying to be used before it's eligible as defined by 'nbf'; or
            // Provided JWT is trying to be used before it's been created as defined by 'iat'
            throw new AssignmentBeforeValidException($e->getMessage(), $e->getCode(), $e);
        } catch (ExpiredException $e) {
            // Provided JWT has since expired, as defined by the 'exp' claim
            throw new AssignmentExpiredException($e->getMessage(), $e->getCode(), $e);
        } catch (UnexpectedValueException $e) {
            // Provided JWT was invalid
            throw new AssignmentUnexpectedValueException($e->getMessage(), $e->getCode(), $e);
        } catch (DomainException $e) {
            // Provided JWT is malformed
            throw new AssignmentDomainException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return stdClass
     */
    protected function doDecode(string $token): object
    {
        return JWT::decode($token, $this->getKey());
    }

    private function getKey(): Key
    {
        return new Key($this->jwtKey, 'HS256');
    }
}

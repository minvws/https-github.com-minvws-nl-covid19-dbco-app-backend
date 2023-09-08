<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Exception\AssignmentBeforeValidException;
use App\Services\Assignment\Exception\AssignmentDomainException;
use App\Services\Assignment\Exception\AssignmentExpiredException;
use App\Services\Assignment\Exception\AssignmentInternalValidationException;
use App\Services\Assignment\Exception\AssignmentSignatureInvalidException;
use App\Services\Assignment\Exception\AssignmentUnexpectedValueException;
use App\Services\Assignment\Exception\Http\AssignmentExpiredTokenHttpException;
use App\Services\Assignment\Exception\Http\AssignmentInvalidTokenHttpException;
use App\Services\Assignment\TokenDecoder\TokenDecoder;
use App\Services\Assignment\TokenFetcher\TokenFetcher;
use App\Services\Assignment\TokenProcessor\JwtTokenProcessor;
use Psr\Log\LoggerInterface;
use stdClass;

final class AssignmentJwtTokenAuthService implements AssignmentTokenAuthService
{
    use AssignmentAllowedByToken;

    /**
     * @param TokenDecoder<stdClass> $tokenDecoder
     */
    public function __construct(
        private readonly TokenFetcher $tokenFetcher,
        private readonly TokenDecoder $tokenDecoder,
        private readonly JwtTokenProcessor $tokenProcessor,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function hasToken(): bool
    {
        return $this->tokenFetcher->hasToken();
    }

    public function getToken(): Token
    {
        $stringToken = $this->tokenFetcher->getToken();

        try {
            $decodedToken = $this->tokenDecoder->decode($stringToken);
        } catch (AssignmentExpiredException) {
            throw new AssignmentExpiredTokenHttpException();
        } catch (
            AssignmentSignatureInvalidException
            | AssignmentBeforeValidException
            | AssignmentUnexpectedValueException
            | AssignmentDomainException
        ) {
            $this->logger->debug('Failed decoding token!', ['stringToken' => $stringToken]);

            throw new AssignmentInvalidTokenHttpException();
        }

        try {
            return $this->tokenProcessor->fromPayload($decodedToken);
        } catch (AssignmentInternalValidationException) {
            $this->logger->debug('Failed processing token!', ['decodedToken' => $decodedToken]);

            throw new AssignmentInvalidTokenHttpException();
        }
    }

    public function allowed(AssignmentModelEnum $model, array $uuids, EloquentUser $user): bool
    {
        if (!$this->hasToken()) {
            return false;
        }

        $token = $this->getToken();
        if ($token->sub !== $user->uuid) {
            return false;
        }

        return $this->allowedByModel($token, $model, $uuids);
    }

    public function allowedCases(array $uuids, EloquentUser $user): bool
    {
        return $this->allowed(AssignmentModelEnum::Case_, $uuids, $user);
    }
}

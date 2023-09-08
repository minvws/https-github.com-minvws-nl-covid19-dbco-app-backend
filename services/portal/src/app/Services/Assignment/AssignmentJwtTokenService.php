<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Exception\AssignmentInvalidValueException;
use App\Services\Assignment\Exception\AssignmentRuntimeException;
use App\Services\Assignment\TokenEncoder\TokenEncoder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Collection;

use function assert;
use function count;
use function is_integer;
use function is_string;
use function sprintf;

final class AssignmentJwtTokenService implements AssignmentTokenService
{
    private readonly string $issuer;
    private readonly string $audience;
    private readonly int $maxCaseUuids;

    /**
     * @param TokenEncoder<Token> $tokenEncoder
     */
    public function __construct(private readonly Config $config, private readonly TokenEncoder $tokenEncoder)
    {
        $issuer = $this->config->get('assignment.jwt.issuer');
        $audience = $this->config->get('assignment.jwt.audience');
        $maxCaseUuids = $this->config->get('assignment.stateless.cases.max_uuids');

        assert(is_string($issuer), AssignmentInvalidValueException::wrongType('issuer', 'string', $issuer));
        assert(is_string($audience), AssignmentInvalidValueException::wrongType('audience', 'string', $audience));
        assert(is_integer($maxCaseUuids), AssignmentInvalidValueException::wrongType('maxCaseUuids', 'integer', $maxCaseUuids));

        $this->issuer = $issuer;
        $this->audience = $audience;
        $this->maxCaseUuids = $maxCaseUuids;
    }

    public function createTokenForCases(array $uuids, EloquentUser $user, int $ttlExpirationInMinutes = 30): string
    {
        assert(
            $ttlExpirationInMinutes > 0 && $ttlExpirationInMinutes <= 1440,
            new AssignmentRuntimeException(
                'You are not allowed to create tokens valid for more than 1440 minutes (1 day)!',
            ),
        );

        if (count($uuids) > $this->maxCaseUuids) {
            throw new AssignmentRuntimeException(
                sprintf('Only allowed to pass "%s" case uuids, given "%s" case uuids.', $this->maxCaseUuids, count($uuids)),
            );
        }

        $resources = Collection::make([new TokenResource(mod: AssignmentModelEnum::Case_, ids: $uuids)]);

        $issuedAt = CarbonImmutable::now();
        $expirationAt = $issuedAt->addMinutes($ttlExpirationInMinutes);

        return $this->encodeToken($resources, $issuedAt, $expirationAt, $user);
    }

    /**
     * @param Collection<int,TokenResource> $tokenResources
     */
    public function createToken(
        Collection $tokenResources,
        EloquentUser $user,
        int $ttlExpirationInMinutes = 30,
    ): string {
        assert(
            $ttlExpirationInMinutes > 0 && $ttlExpirationInMinutes <= 1440,
            new AssignmentRuntimeException(
                'You are not allowed to create tokens valid for more than 1440 minutes (1 day)!',
            ),
        );

        $issuedAt = CarbonImmutable::now();
        $expirationAt = $issuedAt->addMinutes($ttlExpirationInMinutes);

        return $this->encodeToken($tokenResources, $issuedAt, $expirationAt, $user);
    }

    public function getAudience(): string
    {
        return $this->audience;
    }

    public function getIssuer(): string
    {
        return $this->issuer;
    }

    private function encodeToken(
        Collection $resources,
        DateTimeInterface $issuedAt,
        DateTimeInterface $expirationAt,
        EloquentUser $user,
    ): string {
        return $this->tokenEncoder->encode(new Token(
            iss: $this->getIssuer(),
            aud: $this->getAudience(),
            sub: $user->uuid,
            exp: $expirationAt->getTimestamp(),
            iat: $issuedAt->getTimestamp(),
            jti: null,
            res: $resources,
        ));
    }
}

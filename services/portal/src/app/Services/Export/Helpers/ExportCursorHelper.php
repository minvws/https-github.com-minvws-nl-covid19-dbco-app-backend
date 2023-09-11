<?php

declare(strict_types=1);

namespace App\Services\Export\Helpers;

use App\Models\Export\Cursor;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Models\Export\Mutation;
use App\Services\Export\Exceptions\ExportCursorException;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

use function assert;
use function is_int;
use function is_null;
use function is_string;
use function sha1;
use function sprintf;

class ExportCursorHelper
{
    public const EXPIRY_SECONDS = 24 * 60 * 60; // 1 day
    private const JWT_ALGORITHM = 'HS256';

    private Key $jwtKey;

    public function __construct(
        private readonly ExportPseudoIdHelper $pseudoIdHelper,
        private readonly string $jwtSecret,
    ) {
        $this->jwtKey = new Key($this->jwtSecret, self::JWT_ALGORITHM);
    }

    public function isActiveCursorToken(string $cursorToken): bool
    {
        try {
            JWT::decode($cursorToken, $this->jwtKey);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function hashForClient(ExportClient $client): string
    {
        return sha1(JWT::sign((string) $client->id, $this->jwtSecret, self::JWT_ALGORITHM));
    }

    /**
     * @throws ExportCursorException
     */
    private function validateCursorPayload(object $payload, ExportType $type, ExportClient $client): void
    {
        if (empty($payload->sub) || $payload->sub !== $type->value) {
            throw new ExportCursorException('Cursor is not valid in this context');
        }

        $clientHash = $this->hashForClient($client);
        if (empty($payload->aud) || $payload->aud !== $clientHash) {
            throw new ExportCursorException('Cursor is not valid for this client');
        }
    }

    /**
     * @throws ExportCursorException
     */
    public function decodeCursorFromTokenForClient(
        string $cursorToken,
        ExportType $type,
        ExportClient $client,
    ): Cursor {
        try {
            $payload = JWT::decode($cursorToken, $this->jwtKey);
            $this->validateCursorPayload($payload, $type, $client);

            $sinceTimestamp = $payload->s ?? null;
            assert(is_int($sinceTimestamp));
            $since = new DateTimeImmutable(sprintf('@%d', $sinceTimestamp));

            $untilTimestamp = $payload->u ?? null;
            assert(is_null($untilTimestamp) || is_int($untilTimestamp));
            $until = $untilTimestamp === null ? null : new DateTimeImmutable(sprintf('@%d', $untilTimestamp));

            $pseudoId = $payload->pi ?? null;
            $id = is_string($pseudoId) ? $this->pseudoIdHelper->pseudoIdToIdForClient($pseudoId, $client) : null;

            return new Cursor($since, $until, $id);
        } catch (Throwable $e) {
            throw ExportCursorException::from($e);
        }
    }

    public function encodeCursorToTokenForClient(Cursor $cursor, ExportType $type, ExportClient $client): string
    {
        $clientHash = $this->hashForClient($client);

        $payload = [
            'exp' => CarbonImmutable::now()->addSeconds(self::EXPIRY_SECONDS)->getTimestamp(),
            'aud' => $clientHash,
            'sub' => $type->value,
            's' => $cursor->since->getTimestamp(),
        ];

        if ($cursor->until !== null) {
            $payload['u'] = $cursor->until->getTimestamp();
        }

        if ($cursor->id !== null) {
            $pseudoId = $this->pseudoIdHelper->idToPseudoIdForClient($cursor->id, $client);
            $payload['pi'] = $pseudoId;
        }

        return JWT::encode($payload, $this->jwtSecret, self::JWT_ALGORITHM);
    }

    public function createFirstPageCursor(DateTimeInterface $since, ?DateTimeInterface $until = null): Cursor
    {
        return new Cursor($since, $until, null);
    }

    public function createNextPageCursor(Cursor $current, Mutation $lastMutation): Cursor
    {
        $since = $lastMutation->deletedAt ?? $lastMutation->updatedAt ?? CarbonImmutable::now();
        return new Cursor($since, $current->until, $lastMutation->id);
    }
}

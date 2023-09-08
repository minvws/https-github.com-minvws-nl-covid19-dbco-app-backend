<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Export;

use App\Events\Api\Export\InvalidJWTEncountered;
use App\Http\Requests\Api\ApiRequest;
use App\Models\Export\ExportClient;
use App\Services\Export\Helpers\ExportCursorHelper;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

use function assert;
use function event;
use function is_string;

class IndexRequest extends ApiRequest
{
    private const DATE_FORMAT = 'Y-m-d\TH:i:sp';

    public function rules(ExportCursorHelper $cursorHelper): array
    {
        return [
            'cursor' => [
                'string',
                'prohibits:since',
                'prohibits:until',
                fn(string $attribute, string $value, callable $fail) => $this->validateCursorToken($cursorHelper, $value, $fail),
            ],
            'since' => [
                'string',
                'date_format:' . self::DATE_FORMAT,
                'before_or_equal:now',
            ],
            'until' => [
                'string',
                'date_format:' . self::DATE_FORMAT,
                'before_or_equal:now',
                'after:since',
            ],
        ];
    }

    /**
     * @throws AuthenticationException
     */
    private function validateCursorToken(ExportCursorHelper $cursorHelper, string $value, callable $fail): void
    {
        $client = Auth::guard('export')->user();
        assert($client instanceof ExportClient);
        if ($cursorHelper->isActiveCursorToken($value)) {
            return;
        }

        event(new InvalidJWTEncountered($client));
        $fail('Cursor is invalid or expired');
    }

    private function getDate(string $name): ?DateTimeInterface
    {
        $raw = $this->safe([$name])[$name] ?? null;
        if (!is_string($raw)) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $raw);
        assert($date instanceof DateTimeInterface);
        return $date;
    }

    public function getSince(): ?DateTimeInterface
    {
        return $this->getDate('since');
    }

    public function getUntil(): ?DateTimeInterface
    {
        return $this->getDate('until');
    }

    public function getCursor(): ?string
    {
        $cursor = $this->safe(['cursor'])['cursor'] ?? null;
        if (!is_string($cursor)) {
            return null;
        }

        return $cursor;
    }
}

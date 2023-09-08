<?php

declare(strict_types=1);

namespace App\Http\Responses\Callcenter;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task\General;
use App\Services\SearchHash\Dto\SearchHashSourceResult;
use App\Services\SearchHash\Normalizer\HashNormalizer;
use App\Services\SearchHash\SearchResult;
use DateTimeInterface;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\DBCO\Enum\Models\SearchHashResultType;
use RuntimeException;

use function array_search;
use function in_array;
use function is_null;
use function is_object;
use function sprintf;
use function trim;

/**
 * @phpstan-type ModelValue object|string|int|float|bool|null
 * @phpstan-type PersonalDetails Collection<string,array{key:string,value:object|string|int|float|bool|null,isMatch:bool}>
 */
class SearchResultEncoder implements EncodableDecorator
{
    public function __construct(
        private readonly HashNormalizer $hashNormalizer,
    ) {
    }

    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof SearchResult) {
            return;
        }

        $case = $this->getCase($value);
        $personalDetails = $this->getPersonalDetails($value);

        $container->uuid = $case->uuid;
        $container->token = $value->token;
        $container->caseType = $value->searchHashResultType;
        $container->personalDetails = $personalDetails;
        $container->isMatch = $personalDetails->every(static fn ($personalDetail): bool => $personalDetail['isMatch']);

        $this->applyIndexOnlyDataMutations($value, $container);
        $this->applyContactOnlyDataMutations($value, $container);
    }

    protected function formatValue(object|string|int|float|bool|null $value, ?string $key = null): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_object($value)) {
            throw new RuntimeException(sprintf('Unknown object given for key %s instance of %s', $key, $value::class));
        }

        $value = (string) $value;

        if (!empty($key) && $key === 'phone') {
            return PhoneFormatter::format($value);
        }

        return $this->hashNormalizer->normalizeString($value);
    }

    private function isMatch(
        object|string|int|float|bool|null $old,
        object|string|int|float|bool|null $new,
        ?string $key = null,
    ): bool {
        return $this->formatValue($old, $key) === $this->formatValue($new, $key);
    }

    private function getCase(SearchResult $result): EloquentCase
    {
        return $result->searchedModel instanceof EloquentTask
            ? $result->searchedModel->covidCase
            : $result->searchedModel;
    }

    private function getPersonalDetails(SearchResult $result): Collection
    {
        /** @phpstan-var Collection<int,SearchHashSourceResult> $allSearchHashResults */
        $allSearchHashResults = $result
            ->hashesByKey
            ->pluck('sources')
            ->flatten(1);

        /** @phpstan-var PersonalDetails $personalDetails */
        $personalDetails = $allSearchHashResults
            ->mapWithkeys(function (SearchHashSourceResult $sourceResult) use ($result): array {
                /** @var ModelValue $modelValue */
                $modelValue = Arr::get($result->searchedModel, $sourceResult->sourceKey);

                return [
                    $sourceResult->valueObjectKey => [
                        'key' => $sourceResult->valueObjectKey,
                        'value' => $sourceResult->valueObjectValue,
                        'isMatch' => $this->isMatch($sourceResult->valueObjectValue, $modelValue, $sourceResult->valueObjectKey),
                    ],
                ];
            });

        return $this->mergeAddressDetails($personalDetails)->values();
    }

    /**
     * @phpstan-param PersonalDetails $personalDetails
     *
     * @phpstan-return PersonalDetails
     */
    private function mergeAddressDetails(Collection $personalDetails): Collection
    {
        $addressKeys = ['postalCode', 'houseNumber', 'houseNumberSuffix'];
        $addressDetails = $personalDetails
            ->filter(static fn ($personalDetail, $key) => in_array($key, $addressKeys, true))
            ->sort(static fn ($a, $b) => array_search($a['key'], $addressKeys, true) <=> array_search($b['key'], $addressKeys, true))
            ->values();

        return $personalDetails
            ->forget($addressKeys)
            ->when(
                $addressDetails->count() > 0,
                static fn (Collection $personalDetails): Collection
                    => $personalDetails->put('address', [
                        'key' => 'address',
                        'value' => trim($addressDetails->implode('value', ' ')),
                        'isMatch' => $addressDetails->every('isMatch'),
                    ])
            );
    }

    private function applyIndexOnlyDataMutations(SearchResult $result, EncodingContainer $container): void
    {
        if ($result->searchHashResultType !== SearchHashResultType::index()) {
            return;
        }

        $container->testDate = $this->getCase($result)->dateOfTest;
    }

    private function applyContactOnlyDataMutations(SearchResult $result, EncodingContainer $container): void
    {
        if ($result->searchHashResultType !== SearchHashResultType::contact()) {
            return;
        }

        /** @var General $general */
        $general = $result->searchedModel->general;

        $container->dateOfLastExposure = $general->dateOfLastExposure;
    }
}

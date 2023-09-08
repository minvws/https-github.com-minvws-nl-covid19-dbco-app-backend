<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffList;
use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\PurposeDiff;
use App\Schema\Generator\JSONSchema\Diff\Model\PurposeSpecificationDiff;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

use function array_combine;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function ksort;

use const SORT_STRING;

class PurposeSpecification implements Decodable
{
    /** @var array<string, Purpose> */
    public readonly array $purposes;

    /**
     * @param array<Purpose> $purposes
     */
    public function __construct(array $purposes, public readonly ?string $remark)
    {
        $indexedPurposes = array_combine(array_map(static fn ($p) => $p->identifier, $purposes), $purposes);
        ksort($indexedPurposes, SORT_STRING);
        $this->purposes = $indexedPurposes;
    }

    public static function decode(DecodingContainer $container, ?object $object = null): self
    {
        $purposes = $container->{'purposes'}->decodeArrayIfPresent(Purpose::class);
        $remark = $container->{'remark'}->decodeStringIfPresent();
        return new self($purposes ?? [], $remark);
    }

    /**
     * @return array<string>
     */
    private function getPurposeIdentifiers(): array
    {
        return array_keys($this->purposes);
    }

    private function getPurpose(string $identifier): ?Purpose
    {
        return $this->purposes[$identifier] ?? null;
    }

    private function diffPurpose(?Purpose $new, ?Purpose $original): ?PurposeDiff
    {
        if (isset($new) && isset($original)) {
            return $new->diff($original);
        }

        if (isset($new)) {
            return new PurposeDiff(DiffType::Added, $new, null);
        }

        return new PurposeDiff(DiffType::Removed, null, $original);
    }

    public function diff(PurposeSpecification $original): ?PurposeSpecificationDiff
    {
        /** @var array<string> $purposeIdentifiers */
        $purposeIdentifiers = array_unique(array_merge($this->getPurposeIdentifiers(), $original->getPurposeIdentifiers()));

        /** @var DiffList<string, PurposeDiff> $purposeDiffs */
        $purposeDiffs = new DiffList();
        foreach ($purposeIdentifiers as $purposeIdentifier) {
            $newPurpose = $this->getPurpose($purposeIdentifier);
            $originalPurpose = $original->getPurpose($purposeIdentifier);
            $diff = $this->diffPurpose($newPurpose, $originalPurpose);
            if ($diff !== null) {
                $purposeDiffs[$purposeIdentifier] = $diff;
            }
        }

        if ($purposeDiffs->isEmpty()) {
            return null;
        }

        return new PurposeSpecificationDiff(DiffType::Modified, $this, $original, $purposeDiffs);
    }
}

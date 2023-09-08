<?php

declare(strict_types=1);

namespace App\Services\Intake;

use App\Models\Eloquent\EloquentCase;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;

use function array_keys;
use function collect;
use function in_array;

class IntakeConfig
{
    private const CASE_FRAGMENT_FIELDS = [
        'abroad' => [
            'wasAbroad',
            'trips',
        ],
        'contact' => [
            'email',
            'phone',
        ],
        'housemates' => [
            'hasHouseMates',
            'canStrictlyIsolate',
        ],
        'job' => [
            'wasAtJob',
            'sectors',
        ],
        'pregnancy' => [
            'isPregnant',
        ],
        'recentBirth' => [],
        'sourceEnvironments' => [],
        'symptoms' => [
            'hasSymptoms',
            'symptoms',
        ],
        'test' => [
            'dateOfSymptomOnset',
            'isReinfection',
            'previousInfectionDateOfSymptom',
            'dateOfTest',
            'infectionIndicator',
        ],
        'underlyingSuffering' => [
            'hasUnderlyingSufferingOrMedication',
            'hasUnderlyingSuffering',
            'items',
        ],
        'vaccination' => [
            'isVaccinated',
            'vaccineInjections',
        ],
    ];
    private const CASE_REQUIRED_FRAGEMENTS = [
        'test',
    ];

    private const CONTACT_FRAGMENT_FIELDS = [
        'general' => [
            'reference',
        ],
    ];

    private int $caseVersion;
    private array $caseFragmentVersions = [];
    private int $contactVersion;
    private array $contactFragmentVersions = [];

    public function __construct()
    {
        $caseSchemaVersion = EloquentCase::getSchema()->getCurrentVersion();

        $this->caseVersion = $caseSchemaVersion->getVersion();
        foreach ($this->getAllowedCaseFragments() as $fragment) {
            $this->caseFragmentVersions[$fragment] = $caseSchemaVersion
                ->getExpectedField($fragment)
                ->getExpectedType(SchemaType::class)
                ->getSchemaVersion()
                ->getVersion();
        }

        $contactSchemaVersion = $caseSchemaVersion
            ->getExpectedField('tasks')
            ->getExpectedType(ArrayType::class)
            ->getExpectedElementType(SchemaType::class)
            ->getSchemaVersion();


        $this->contactVersion = $contactSchemaVersion->getVersion();
        foreach ($this->getAllowedContactFragments() as $fragment) {
            $this->contactFragmentVersions[$fragment] = $caseSchemaVersion
                ->getExpectedField($fragment)
                ->getExpectedType(SchemaType::class)
                ->getSchemaVersion()
                ->getVersion();
        }
    }

    public function getVersionForCase(): int
    {
        return $this->caseVersion;
    }

    /**
     * @return array<string>
     */
    public function getAllowedCaseFragments(): array
    {
        return collect(self::CASE_REQUIRED_FRAGEMENTS)
            ->merge(collect(self::CASE_FRAGMENT_FIELDS)->keys())
            ->unique()
            ->toArray();
    }

    public function getVersionForCaseFragment(string $fragmentName): int
    {
        return $this->caseFragmentVersions[$fragmentName] ?? 1;
    }

    /**
     * @return array<string>
     */
    public function getAllowedFieldsForCaseFragment(string $fragmentName): array
    {
        return self::CASE_FRAGMENT_FIELDS[$fragmentName] ?? [];
    }

    public function getVersionForContact(): int
    {
        return $this->contactVersion;
    }

    /**
     * @return array<string>
     */
    public function getAllowedContactFragments(): array
    {
        return array_keys(self::CONTACT_FRAGMENT_FIELDS);
    }

    public function getVersionForContactFragment(string $fragmentName): int
    {
        return $this->contactFragmentVersions[$fragmentName] ?? 1;
    }

    /**
     * @return array<string>
     */
    public function getAllowedFieldsForContactFragment(string $fragmentName): array
    {
        return self::CONTACT_FRAGMENT_FIELDS[$fragmentName] ?? [];
    }

    public function isFragmentRequired(string $fragmentName): bool
    {
        return in_array($fragmentName, self::CASE_REQUIRED_FRAGEMENTS, true);
    }
}

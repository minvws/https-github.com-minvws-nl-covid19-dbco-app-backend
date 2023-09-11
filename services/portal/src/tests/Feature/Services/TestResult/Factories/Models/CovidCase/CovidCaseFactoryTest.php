<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Eloquent\EloquentCase;
use App\Schema\Fields\Field;
use App\Schema\SchemaObject;
use App\Schema\SchemaVersion;
use App\Schema\Types\SchemaType;
use App\Services\TestResult\Factories\Models\CovidCase\CovidCaseFactory;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\Http\Middleware\FeatureTest;

use function array_filter;
use function array_map;
use function array_values;
use function is_a;

final class CovidCaseFactoryTest extends FeatureTest
{
    #[TestDox('Test ResultCovidCaseFactory returns latest version of CovidCase')]
    public function testTestResultCovidCaseFactoryReturnsLatestVersionOfCovidCase(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $organisation = $this->createOrganisation(['bco_phase' => BCOPhase::phaseNone()]);

        $covidCase = CovidCaseFactory::create($testResultReport, $organisation, null);

        $currentEloquentCaseSchemaVersion = EloquentCase::getSchema()->getCurrentVersion();

        $this->assertSame(
            $currentEloquentCaseSchemaVersion->getVersion(),
            $covidCase->getSchemaVersion()->getVersion(),
            "Either upgrade the ESB version directly (when desirable and no version-flag is used) or mark as skipped and create a ticket for when the version bump is completed",
        );
    }

    public function testThatSchemaAndInstanceHaveTheSameFieldVersions(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $organisation = $this->createOrganisation(['bco_phase' => BCOPhase::phaseNone()]);

        $covidCase = CovidCaseFactory::create($testResultReport, $organisation, null);
        $covidCaseSchemaVersion = $covidCase->getSchemaVersion();

        $schemaFields = array_values(array_map(
            static fn (Field $field) => $field->getName(),
            array_filter($covidCaseSchemaVersion->getFields(), static fn(Field $field) => is_a($field->getType(), SchemaType::class)),
        ));
        $schemaFieldsOnInstance = array_filter($schemaFields, static fn (string $field) => isset($covidCase->{$field}));

        $this->assertEquals(
            $this->getVersionsFromSchemaVersion($covidCaseSchemaVersion, $schemaFieldsOnInstance),
            $this->getVersionsFromEloquentCaseInstance($covidCase, $schemaFieldsOnInstance),
        );
    }

    /**
     * @template T of EloquentCase&SchemaObject
     *
     * @param SchemaVersion<T> $schemaVersion
     * @param array<string> $fields
     */
    private function getVersionsFromSchemaVersion(SchemaVersion $schemaVersion, array $fields): array
    {
        return Collection::make($fields)
            ->mapWithKeys(function (string $field) use ($schemaVersion): array {
                return [$field => $this->getFieldVersion($schemaVersion, $field)];
            })
            ->toArray();
    }

    /**
     * @param array<string> $fields
     */
    private function getVersionsFromEloquentCaseInstance(EloquentCase $case, array $fields): array
    {
        return Collection::make($fields)
            ->mapWithKeys(static function (string $field) use ($case): array {
                return [$field => $case->{$field}->getSchemaVersion()->getVersion()];
            })
            ->toArray();
    }

    /**
     * @template T of EloquentCase&SchemaObject
     *
     * @param SchemaVersion<T> $schemaVersion
     */
    private function getFieldVersion(SchemaVersion $schemaVersion, string $field): int
    {
        return $schemaVersion
            ->getExpectedField($field)
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();
    }
}

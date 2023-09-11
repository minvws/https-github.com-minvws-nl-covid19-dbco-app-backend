<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Fields;

use App\Models\Fields\PlainCSVContentRetriever;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Purpose\Purpose;
use App\Schema\Schema;
use App\Schema\SchemaCache;
use App\Schema\SchemaProvider;
use Tests\Feature\FeatureTestCase;
use Tests\Feature\Models\Fields\Dummy\DummySchemaProvider;
use Tests\Feature\Models\Fields\Dummy\NestedDummySchemaProvider;
use Throwable;

use function array_shift;
use function config;
use function fgetcsv;
use function file_exists;
use function fopen;
use function PHPUnit\Framework\assertEquals;
use function resource_path;
use function unlink;

class DataCatalogExportTest extends FeatureTestCase
{
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        SchemaCache::clear();

        //if data/test-data.json exists, delete it
        $testDataPath = resource_path('data/temp-test-datacatalog.csv');
        if (file_exists($testDataPath)) {
            unlink($testDataPath);
        }
    }

    //test that the datacatalog.csv file is created
    public function testExportDataCatalog(): void
    {
        $this->artisan('schema:generate-datacatalog-csv', ['filename' => 'temp-test-datacatalog.csv'])
            ->expectsOutput('Generate datacatalog CSV...')
            ->assertExitCode(0);
    }

    //test that the datacatalog.csv has the correct number of columns
    public function testExportDataCatalogColumns(): void
    {
        $this->artisan('schema:generate-datacatalog-csv', ['filename' => 'temp-test-datacatalog.csv']);
        $file = fopen(resource_path('data/temp-test-datacatalog.csv'), 'r');
        $columns = fgetcsv($file);

        $this->assertCount(12, $columns);
    }

    public function testItUsesConfiguredCSVForTranslations(): void
    {
        config([
            'schema.readTranslationsFrom' => __DIR__ . '/Dummy/DummyCatalog.csv',
            'schema.classes' => [
                DummySchemaProvider::class,
            ],
        ]);
        $output = [
            [
                "DummySchemaProvider.schemaVersion",
                "Versie field",
                "Versienummer van de entiteit",
            ],
            [
                "DummySchemaProvider.fieldWithPurpose",
                "Field with purpose",
                "Field with purpose",
            ],
            [
                "DummySchemaProvider.fieldWithoutPurpose",
                "Field without purpose",
                "Field without purpose",
            ],
        ];

        foreach (DummySchemaProvider::getSchema()->getFields() as $key => $field) {
            assertEquals($output[$key][0], $field->getDocumentationIdentifier());
            assertEquals($output[$key][1], $field->getDocumentation()->getLabel());
            assertEquals($output[$key][2], $field->getDocumentation()->getDescription());
        }
    }

    public function testItKeepsPurposeSpecificationsOn(): void
    {
        config([
            'schema.readTranslationsFrom' => __DIR__ . '/Dummy/DummyCatalog.csv',
            'schema.classes' => [
                DummySchemaProvider::class,
                NestedDummySchemaProvider::class,
            ],
        ]);

        $instance = new PurposeSpecificationBinder(new PlainCSVContentRetriever(__DIR__ . '/Dummy/DummyCatalog.csv'));
        $this->app->instance(PurposeSpecificationBinder::class, $instance);
        $this->artisan('schema:generate-datacatalog-csv', ['filename' => 'temp-test-datacatalog.csv']);

        //Get csv content as array
        $file = fopen(resource_path('data/temp-test-datacatalog.csv'), 'r');
        $csv = [];
        while (($line = fgetcsv($file)) !== false) {
            $csv[] = $line;
        }

        //pop off the header row
        array_shift($csv);

        foreach ($csv as $row) {
            $purposes[$row[0] . '.' . $row[1]][Purpose::EpidemiologicalSurveillance->value] = $row[6];
            $purposes[$row[0] . '.' . $row[1]][Purpose::QualityOfCare->value] = $row[7];
            $purposes[$row[0] . '.' . $row[1]][Purpose::AdministrativeAdvice->value] = $row[8];
            $purposes[$row[0] . '.' . $row[1]][Purpose::OperationalAdjustment->value] = $row[9];
            $purposes[$row[0] . '.' . $row[1]][Purpose::ScientificResearch->value] = $row[10];
            $purposes[$row[0] . '.' . $row[1]][Purpose::ToBeDetermined->value] = $row[11];
        }

        foreach (config('schema.classes') as $class) {
            $schema = $instance->bind($class::getSchema());
            foreach ($schema->getFields() as $field) {
                $id = $field->getDocumentationIdentifier();
                $purposeSpecification = $field->getPurposeSpecification();

                assertEquals(
                    $purposes[$id][Purpose::EpidemiologicalSurveillance->value],
                    $purposeSpecification->getPurposeDetail(Purpose::EpidemiologicalSurveillance)?->subPurpose->getLabel(),
                );
                assertEquals(
                    $purposes[$id][Purpose::QualityOfCare->value],
                    $purposeSpecification->getPurposeDetail(Purpose::QualityOfCare)?->subPurpose->getLabel(),
                );
                assertEquals(
                    $purposes[$id][Purpose::AdministrativeAdvice->value],
                    $purposeSpecification->getPurposeDetail(Purpose::AdministrativeAdvice)?->subPurpose->getLabel(),
                );
                assertEquals(
                    $purposes[$id][Purpose::OperationalAdjustment->value],
                    $purposeSpecification->getPurposeDetail(Purpose::OperationalAdjustment)?->subPurpose->getLabel(),
                );
                assertEquals(
                    $purposes[$id][Purpose::ScientificResearch->value],
                    $purposeSpecification->getPurposeDetail(Purpose::ScientificResearch)?->subPurpose->getLabel(),
                );
                assertEquals(
                    $purposes[$id][Purpose::ToBeDetermined->value],
                    $purposeSpecification->getPurposeDetail(Purpose::ToBeDetermined)?->subPurpose->getLabel(),
                );
            }
        }
    }

    public function testItThrowsExceptionWhenSchemaClassDoesNotImplementSchemaProviderInterface(): void
    {
        $class = new class {
        };
        config([
            'schema.classes' => [
                $class::class,
            ],
        ]);

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($class::class . " does not implement " . SchemaProvider::class . " interface!");
        $this->artisan('schema:generate-datacatalog-csv', ['filename' => 'temp-test-datacatalog.csv']);
    }

    public function testItThrowsExceptionWhenSchemaClassDoesNotUseVersionedClasses(): void
    {
        $class = new class implements SchemaProvider {
            public static function getSchema(): Schema
            {
                $schema = new Schema(self::class);
                $schema->setUseVersionedClasses(false);
                return $schema;
            }
        };
        config([
            'schema.classes' => [
                $class::class,
            ],
        ]);

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($class::class . " is not using versioned classes!");
        $this->artisan('schema:generate-datacatalog-csv', ['filename' => 'temp-test-datacatalog.csv']);
    }
}

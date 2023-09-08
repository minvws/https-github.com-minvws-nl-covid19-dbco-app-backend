<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Fields;

use App\Models\Fields\PlainCSVContentRetriever;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Purpose\CSVPurpose;
use App\Models\Purpose\Purpose;
use App\Models\Purpose\SubPurpose;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Throwable;

use function class_exists;
use function file_exists;
use function resource_path;

#[Group('PurposeSpecificationBinder')]
class PurposeSpecificationBinderTest extends FeatureTestCase
{
    private PurposeSpecificationBinder $binder;

    protected function setUp(): void
    {
        parent::setUp();

        $contentRetriever = new PlainCSVContentRetriever(__DIR__ . '/Dummy/DummyCatalog.csv');
        $this->binder = new PurposeSpecificationBinder($contentRetriever);
    }

    #[DataProvider('schemaIdentifierProvider')]
    public function testItSetsNoPurposeForXThatHasAPurposeSpecifiedInCode(string $schemaIdentifier): void
    {
        $schema = new Schema($schemaIdentifier);
        $schema->setCurrentVersion(1);
        $schema->setDocumentationIdentifier($schemaIdentifier);
        $schema->add(StringType::createField('fieldWithoutPurpose'))->specifyPurpose(
            static fn(PurposeSpecificationBuilder $builder) => $builder->addPurpose(
                Purpose::EpidemiologicalSurveillance,
                SubPurpose::Schools,
            )
        );
        $field = $schema->getFields()[1];
        $this->binder->bind($schema);
        $purposeSpecification = $field->getPurposeSpecification();

        $this->assertEquals(SubPurpose::Schools, $purposeSpecification->getPurposeDetail(Purpose::EpidemiologicalSurveillance)->subPurpose);
        $this->assertNull($purposeSpecification->getPurposeDetail(Purpose::QualityOfCare));
        $this->assertNull($purposeSpecification->getPurposeDetail(Purpose::ScientificResearch));
        $this->assertNull($purposeSpecification->getPurposeDetail(Purpose::OperationalAdjustment));
        $this->assertNull($purposeSpecification->getPurposeDetail(Purpose::AdministrativeAdvice));
    }

    #[DataProvider('schemaIdentifierProvider')]
    public function testItSetsALabelForXThatHasAPrivacyNote(string $schemaIdentifier, string $expectedRemark): void
    {
        $schema = new Schema($schemaIdentifier);
        $schema->setCurrentVersion(1);
        $schema->setDocumentationIdentifier($schemaIdentifier);
        $schema->add(StringType::createField('fieldWithoutPurpose'));
        $field = $schema->getFields()[1];

        $this->assertFalse($field->hasPurposeSpecification());
        $this->binder->bind($schema);
        $this->assertTrue($field->hasPurposeSpecification());

        $purposeSpecification = $field->getPurposeSpecification();
        $remarkFromSheet = $purposeSpecification->remark;
        self::assertEquals($expectedRemark, $remarkFromSheet);
    }

    #[DataProvider('schemaIdentifierProvider')]
    public function testItSetsAPurposeForXThatHasNoPurposeSpecifiedInCode(string $schemaIdentifier): void
    {
        $schema = new Schema($schemaIdentifier);
        $schema->setCurrentVersion(1);
        $schema->setDocumentationIdentifier($schemaIdentifier);
        $schema->add(StringType::createField('fieldWithoutPurpose'));
        $field = $schema->getFields()[1];

        $this->assertFalse($field->hasPurposeSpecification());
        $this->binder->bind($schema);
        //But since the binder has set a purpose, hasPurposeSpecification should now return true
        $this->assertTrue($field->hasPurposeSpecification());

        $purposeSpecification = $field->getPurposeSpecification();

        $this->assertEquals(SubPurpose::Schools, $purposeSpecification->getPurposeDetail(Purpose::EpidemiologicalSurveillance)->subPurpose);
        $this->assertEquals(SubPurpose::Linking, $purposeSpecification->getPurposeDetail(Purpose::QualityOfCare)->subPurpose);
        $this->assertEquals(SubPurpose::Timeliness, $purposeSpecification->getPurposeDetail(Purpose::ScientificResearch)->subPurpose);
        $this->assertEquals(SubPurpose::ByHealthAuthorities, $purposeSpecification->getPurposeDetail(Purpose::ToBeDetermined)->subPurpose);

        $this->assertNull($purposeSpecification->getPurposeDetail(Purpose::OperationalAdjustment));
        $this->assertNull($purposeSpecification->getPurposeDetail(Purpose::AdministrativeAdvice));
    }

    public function testCSVPurposeThrowsExceptionWhenEncounteringNonExistentPurpose(): void
    {
        $nonExistentPurpose = 'non-existent-purpose';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid subpurpose: ' . $nonExistentPurpose);
        CSVPurpose::fromCSVString($nonExistentPurpose);
    }

    public function testItThrowsAnErrorWhenItEncountersAWrongAmountOfNesting(): void
    {
        $schema = new Schema('foo');
        $schema->setCurrentVersion(1);
        $schema->setDocumentationIdentifier('foo.bar.foo');
        $this->assertThrows(function () use ($schema): void {
            $this->binder->bind($schema);
        }, Throwable::class, '4 level nesting is not supported');
    }

    public static function schemaIdentifierProvider(): array
    {
        return [
            'nested field' => [
                'DummySchemaProvider.NestedDummySchemaProvider',
                'Lorem ipsum schools2',
            ],
            'plain field' => [
                'DummySchemaProvider',
                'Lorem ipsum schools',
            ],
        ];
    }

    public function testTheHelperHasBeenAddedToTheCodebase(): void
    {
        $this->assertTrue(class_exists(PurposeSpecificationBinder::class));
    }

    public function testCsvHasBeenAddedToTheCodebase(): void
    {
        $this->assertTrue(file_exists(resource_path('/data/datacatalog.csv')));
    }
}

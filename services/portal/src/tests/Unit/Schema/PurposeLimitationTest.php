<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Models\Purpose\Purpose;
use App\Models\Purpose\SubPurpose;
use App\Schema\Entity;
use App\Schema\Purpose\PurposeLimitationBuilder;
use App\Schema\Purpose\PurposeLimitedEncodingContext;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Schema;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use Generator;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function array_keys;
use function assert;

#[Group('schema')]
#[Group('schema-purpose')]
class PurposeLimitationTest extends UnitTestCase
{
    private Schema $schema;

    protected function setUp(): void
    {
        parent::setUp();

        $schema = new Schema(Entity::class);
        $schema->setCurrentVersion(1);

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasSymptoms'))
            ->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
                $builder->addPurposes(Purpose::cases(), SubPurpose::InterpretEpiCurve);
            });

        $schema->add(IntType::createField('daysSinceFirstSymptom'))
            ->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
                $builder->addPurpose(Purpose::EpidemiologicalSurveillance, SubPurpose::Timeliness);
            });

        $schema->add(Symptom::getVersion(1)->createArrayField('symptoms'))
            ->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
                $builder->addPurposes(Purpose::cases(), SubPurpose::InterpretEpiCurve);
            });

        $schema->add(StringType::createField('remark'))
            ->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
                $builder->addPurpose(Purpose::QualityOfCare, SubPurpose::EpiCurve);
            });

        $this->schema = $schema;
    }

    public function testLimitation(): void
    {
        $purposeLimitation = PurposeLimitationBuilder::create()->addPurpose(Purpose::EpidemiologicalSurveillance)->build();
        $this->assertTrue($purposeLimitation->hasPurpose(Purpose::EpidemiologicalSurveillance));
        $this->assertFalse($purposeLimitation->hasPurpose(Purpose::OperationalAdjustment));
        $this->assertEquals([Purpose::EpidemiologicalSurveillance], $purposeLimitation->getPurposes());

        $purposeLimitation = PurposeLimitationBuilder::create()
            ->addPurpose(Purpose::EpidemiologicalSurveillance)
            ->addPurpose(Purpose::OperationalAdjustment)
            ->build();
        $this->assertTrue($purposeLimitation->hasPurpose(Purpose::EpidemiologicalSurveillance));
        $this->assertTrue($purposeLimitation->hasPurpose(Purpose::OperationalAdjustment));
        $this->assertFalse($purposeLimitation->hasPurpose(Purpose::QualityOfCare));
        $this->assertEquals([Purpose::EpidemiologicalSurveillance, Purpose::OperationalAdjustment], $purposeLimitation->getPurposes());

        $purposeLimitation = PurposeLimitationBuilder::create()->setPurpose(Purpose::EpidemiologicalSurveillance)->build();
        $this->assertTrue($purposeLimitation->hasPurpose(Purpose::EpidemiologicalSurveillance));
        $this->assertFalse($purposeLimitation->hasPurpose(Purpose::OperationalAdjustment));
        $this->assertEquals([Purpose::EpidemiologicalSurveillance], $purposeLimitation->getPurposes());
    }

    public static function purposeProvider(): Generator
    {
        yield [[], []];

        yield [[Purpose::EpidemiologicalSurveillance], ['hasSymptoms', 'daysSinceFirstSymptom', 'symptoms']];

        yield [[Purpose::OperationalAdjustment], ['hasSymptoms', 'symptoms']];

        yield [[Purpose::EpidemiologicalSurveillance, Purpose::QualityOfCare], ['hasSymptoms', 'daysSinceFirstSymptom', 'symptoms', 'remark']];
    }

    /**
     * @param array<Purpose> $purposes
     * @param array<string> $expectedKeys
     */
    #[DataProvider('purposeProvider')]
    public function testEncodeWithPurposeLimitedContext(array $purposes, array $expectedKeys): void
    {
        $purposeLimitation = PurposeLimitationBuilder::create()->addPurposes($purposes)->build();
        $context = new PurposeLimitedEncodingContext($purposeLimitation);
        $context->setMode(EncodingContext::MODE_EXPORT);
        $context->setUseAssociativeArraysForObjects(true);

        $encoder = new Encoder($context);
        $data = $encoder->encode($this->createEntity());

        $this->assertEquals($expectedKeys, array_keys($data));
    }

    /**
     * @param array<Purpose> $purposes
     * @param array<string> $expectedKeys
     */
    #[DataProvider('purposeProvider')]
    public function testEncodeWithChildPurposeLimitedContext(array $purposes, array $expectedKeys): void
    {
        $purposeLimitation = PurposeLimitationBuilder::create()->addPurposes($purposes)->build();
        $context = new PurposeLimitedEncodingContext($purposeLimitation);
        $context->setMode(EncodingContext::MODE_EXPORT);
        $context->setUseAssociativeArraysForObjects(true);

        $encoder = new Encoder($context->createChildContext());
        $data = $encoder->encode($this->createEntity());

        $this->assertEquals($expectedKeys, array_keys($data));
    }

    public function testEncodeWithNormalContext(): void
    {
        $context = new EncodingContext();
        $context->setMode(EncodingContext::MODE_EXPORT);
        $context->setUseAssociativeArraysForObjects(true);

        $encoder = new Encoder($context);
        $data = $encoder->encode($this->createEntity());

        $this->assertEquals(['schemaVersion', 'hasSymptoms', 'daysSinceFirstSymptom', 'symptoms', 'remark'], array_keys($data));
    }

    private function createEntity(): Entity
    {
        $entity = $this->schema->getCurrentVersion()->newInstance();
        assert($entity instanceof Entity);
        $entity->hasSymptoms = YesNoUnknown::yes();
        $entity->daysSinceFirstSymptom = 3;
        $entity->symptoms = [Symptom::dizziness(), Symptom::cough()];
        $entity->remark = 'This is a remark';
        return $entity;
    }
}

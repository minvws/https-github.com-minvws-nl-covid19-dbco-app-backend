<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Conditions\Condition;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema')]
#[Group('schema-codable')]
class CodableTest extends UnitTestCase
{
    private Schema $schema;

    protected function setUp(): void
    {
        parent::setUp();

        $schema = new Schema(Entity::class);

        $schema->setCurrentVersion(1);
        $schema->add(YesNoUnknown::getVersion(1)->createField('hasSymptoms'));

        $condition = Condition::field('hasSymptoms')->equalTo(YesNoUnknown::yes());
        $schema->add(IntType::createField('daysSinceFirstSymptom'))->setCodingCondition($condition);
        $schema->add(Symptom::getVersion(1)->createArrayField('symptoms'))->setCodingCondition($condition);
        $schema->add(StringType::createField('remark'));

        $this->schema = $schema;
    }

    public function testEncode(): void
    {
        /** @var Entity $entity */
        $entity = $this->schema->getCurrentVersion()->newInstance();
        $entity->hasSymptoms = YesNoUnknown::yes();
        $entity->daysSinceFirstSymptom = 3;
        $entity->symptoms = [Symptom::dizziness(), Symptom::cough()];
        $entity->remark = 'This is a remark';

        $encoder = new Encoder();
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);

        $data = $encoder->encode($entity);
        $this->assertEquals(
            [
                'hasSymptoms' => 'yes',
                'daysSinceFirstSymptom' => 3,
                'symptoms' => ['dizziness', 'cough'],
                'remark' => 'This is a remark',
                'schemaVersion' => 1,
            ],
            $data,
        );

        $entity->hasSymptoms = YesNoUnknown::no();

        $data = $encoder->encode($entity);
        $this->assertEquals(
            [
                'hasSymptoms' => 'no',
                'schemaVersion' => 1,
                'remark' => 'This is a remark',
            ],
            $data,
        );
    }

    public function testDecode(): void
    {
        $data = [
            'hasSymptoms' => 'yes',
            'daysSinceFirstSymptom' => 3,
            'symptoms' => ['dizziness', 'cough'],
            'remark' => 'This is a remark',
        ];

        $decoder = new Decoder();
        $container = $decoder->decode($data);
        $entity = $this->schema->getCurrentVersion()->decode($container);
        $this->assertSame(YesNoUnknown::yes(), $entity->hasSymptoms);
        $this->assertEquals(3, $entity->daysSinceFirstSymptom);
        $this->assertSame([Symptom::dizziness(), Symptom::cough()], $entity->symptoms);
        $this->assertEquals('This is a remark', $entity->remark);

        $data = [
            'hasSymptoms' => 'no',
            'daysSinceFirstSymptom' => 3,
            'symptoms' => ['dizziness', 'cough'],
            'remark' => 'This is a remark',
        ];

        $decoder = new Decoder();
        $container = $decoder->decode($data);
        $this->schema->getCurrentVersion()->decode($container, $entity);
        $this->assertSame(YesNoUnknown::no(), $entity->hasSymptoms);
        $this->assertNull($entity->daysSinceFirstSymptom);
        $this->assertNull($entity->symptoms);
        $this->assertEquals('This is a remark', $entity->remark);
    }
}

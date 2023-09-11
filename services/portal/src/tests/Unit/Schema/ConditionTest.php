<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Conditions\Condition;
use App\Schema\Conditions\ConditionHelper;
use App\Schema\Documentation\Documentation;
use App\Schema\Documentation\DocumentationProvider;
use App\Schema\Documentation\LaravelDocumentationProvider;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use DateTime;
use DateTimeZone;
use Generator;
use MinVWS\Codable\EncodingContext;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('schema')]
#[Group('schema-condition')]
class ConditionTest extends TestCase
{
    private ?DocumentationProvider $previousDocumentationProvider = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousDocumentationProvider = Documentation::getProvider();
        Documentation::setProvider(new LaravelDocumentationProvider('schema', 'en'));
    }

    protected function tearDown(): void
    {
        Documentation::setProvider($this->previousDocumentationProvider);

        parent::tearDown();
    }

    public static function stringProvider(): Generator
    {
        yield 'a === b' => [
            Condition::field('a')->identicalTo(Condition::field('b')),
        ];

        yield 'a == b' => [
            Condition::field('a')->equalTo(Condition::field('b')),
        ];

        yield 'a < b' => [
            Condition::field('a')->lessThan(Condition::field('b')),
        ];

        yield 'a > b' => [
            Condition::field('a')->greaterThan(Condition::field('b')),
        ];

        yield 'a <= b' => [
            Condition::field('a')->lessThanOrEqualTo(Condition::field('b')),
        ];

        yield 'a >= b' => [
            Condition::field('a')->greaterThanOrEqualTo(Condition::field('b')),
        ];

        yield 'not (a === b)' => [
            Condition::field('a')->notIdenticalTo(Condition::field('b')),
        ];

        yield 'not (a == b)' => [
            Condition::field('a')->notEqualTo(Condition::field('b')),
        ];

        yield 'a == 1' => [
            Condition::field('a')->equalTo(1),
        ];

        yield 'a === true' => [
            Condition::field('a')->identicalTo(true),
        ];

        yield "a === 'string'" => [
            Condition::field('a')->identicalTo('string'),
        ];

        yield "a > '2021-01-02T12:34:56+00:00'" => [
            Condition::field('a')->greaterThan(new DateTime('2021-01-02 12:34:56+00:00', new DateTimeZone('UTC'))),
        ];

        yield "(a == b and c == d) or not (e > f)" => [
            Condition::field('a')->equalTo(Condition::field('b'))->and(
                Condition::field('c')->equalTo(Condition::field('d')),
            )->or(
                Condition::field('e')->greaterThan(Condition::field('f'))->negate(),
            ),
        ];
    }

    #[DataProvider('stringProvider')]
    public function testStringRepresentation(Condition $condition): void
    {
        $this->assertEquals($this->dataName(), (string) $condition);
    }

    public static function evaluationProvider(): Generator
    {
        yield 'a = 1, b = 1, a === b' => [
            Condition::field('a')->identicalTo(Condition::field('b')),
            ['a', 'b'],
            ['a' => 1, 'b' => 1],
            true,
        ];

        yield "a = 1, b = 2, a === b" => [
            Condition::field('a')->identicalTo(Condition::field('b')),
            ['a', 'b'],
            ['a' => 1, 'b' => 2],
            false,
        ];

        yield "a = 1, b = '1', a === b" => [
            Condition::field('a')->identicalTo(Condition::field('b')),
            ['a', 'b'],
            ['a' => 1, 'b' => '1'],
            false,
        ];

        yield "a = 1, b = '1', a == b" => [
            Condition::field('a')->equalTo(Condition::field('b')),
            ['a', 'b'],
            ['a' => '1', 'b' => '1'],
            true,
        ];

        yield 'a = 1, a === 1' => [
            Condition::field('a')->identicalTo(1),
            ['a'],
            ['a' => 1],
            true,
        ];

        yield "a = 2, b = 1, a > b" => [
            Condition::field('a')->greaterThan(Condition::field('b')),
            ['a', 'b'],
            ['a' => 2, 'b' => 1],
            true,
        ];

        yield "a = 2, a > 3" => [
            Condition::field('a')->greaterThan(3),
            ['a'],
            ['a' => 2],
            false,
        ];

        yield "a = 5, b = 3, c = 2, (a > c and b <= 4)" => [
            Condition::field('a')->greaterThan(Condition::field('c'))->and(
                Condition::field('b')->lessThanOrEqualTo(4),
            ),
            ['a', 'b', 'c'],
            ['a' => 5, 'b' => 3, 'c' => 2],
            true,
        ];

        yield "a = 3, b = 4, c = 2, (a > c and b <= 4)" => [
            Condition::field('a')->greaterThan(Condition::field('c'))->and(
                Condition::field('b')->lessThanOrEqualTo(4),
            ),
            ['a', 'b', 'c'],
            ['a' => 3, 'b' => 4, 'c' => 2],
            true,
        ];

        yield "a = 3, b = 5, c = 2, (a > c and b <= 4)" => [
            Condition::field('a')->greaterThan(Condition::field('c'))->and(
                Condition::field('b')->lessThanOrEqualTo(4),
            ),
            ['a', 'b', 'c'],
            ['a' => 3, 'b' => 5, 'c' => 2],
            false,
        ];
    }

    #[DataProvider('evaluationProvider')]
    public function testEvaluation(Condition $condition, array $fields, array $data, bool $expected): void
    {
        $this->assertEqualsCanonicalizing($fields, $condition->getFields());
        $this->assertEquals($expected, $condition->evaluate($data));
    }

    public function testSortedFields(): void
    {
        $conditionHelper = new ConditionHelper();
        $conditionHelper->add('a', null);
        $conditionHelper->add('b', null);
        $conditionHelper->add('c', null);
        $conditionHelper->add('d', null);
        $this->assertEquals(['a', 'b', 'c', 'd'], $conditionHelper->getSortedFields());

        $conditionHelper = new ConditionHelper();
        $conditionHelper->add('a', null);
        $conditionHelper->add('b', Condition::field('a')->lessThan(1));
        $conditionHelper->add('c', null);
        $conditionHelper->add('d', null);
        $this->assertEquals(['a', 'b', 'c', 'd'], $conditionHelper->getSortedFields());

        $conditionHelper = new ConditionHelper();
        $conditionHelper->add('a', null);
        $conditionHelper->add('b', Condition::field('a')->lessThan(Condition::field('c')));
        $conditionHelper->add('c', null);
        $conditionHelper->add('d', null);
        $this->assertEquals(['a', 'c', 'b', 'd'], $conditionHelper->getSortedFields());

        $conditionHelper = new ConditionHelper();
        $conditionHelper->add('a', Condition::field('d')->identicalTo(1));
        $conditionHelper->add('b', Condition::field('c')->identicalTo(2));
        $conditionHelper->add('c', Condition::field('a')->identicalTo(3));
        $conditionHelper->add('d', null);
        $this->assertEquals(['d', 'a', 'c', 'b'], $conditionHelper->getSortedFields());
    }

    public function testCircularDependencyException(): void
    {
        $this->expectException(CircularDependencyException::class);
        $conditionHelper = new ConditionHelper();
        $conditionHelper->add('a', Condition::field('b')->identicalTo(1));
        $conditionHelper->add('b', Condition::field('c')->identicalTo(2));
        $conditionHelper->add('c', Condition::field('a')->identicalTo(3));
        $conditionHelper->getSortedFields();
    }

    public function testElementNotFoundException(): void
    {
        $this->expectException(ElementNotFoundException::class);
        $conditionHelper = new ConditionHelper();
        $conditionHelper->add('a', Condition::field('b')->identicalTo(1));
        $conditionHelper->add('b', Condition::field('c')->identicalTo(2));
        $conditionHelper->getSortedFields();
    }

    public function testConditionResultReuse(): void
    {
        $invokeCount = 0;
        $invokeData = null;
        $condition = Condition::custom(['isEnabled'], static function (array $data) use (&$invokeCount, &$invokeData) {
            $invokeCount++;
            $invokeData = $data;
            return true;
        });

        $schema = new Schema(Entity::class);
        $schema->add(BoolType::createField('isEnabled'));
        $schema->add(StringType::createField('firstField'))->setCodingCondition($condition);
        $schema->add(StringType::createField('secondField'))->setCodingCondition($condition);

        $conditionHelper = new ConditionHelper();
        foreach ($schema->getCurrentVersion()->getFields() as $field) {
            $conditionHelper->add($field->getName(), $field->getEncodingCondition(EncodingContext::MODE_STORE));
        }

        $object = $schema->getCurrentVersion()->newInstance();
        $object->isEnabled = true;
        $object->firstField = 'a';
        $object->secondField = 'b';

        $this->assertEquals(0, $invokeCount);
        $conditionHelper->evaluate('firstField', $object);
        $this->assertEquals(1, $invokeCount);
        $this->assertEquals(['isEnabled' => true], $invokeData);
        $conditionHelper->evaluate('secondField', $object);
        $this->assertEquals(1, $invokeCount);
    }
}

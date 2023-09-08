<?php

declare(strict_types=1);

namespace Tests\Feature\Rules;

use App\Models\Eloquent\EloquentCase;
use App\Rules\ExistsRule;
use stdClass;
use Tests\Feature\FeatureTestCase;

class ExistsRuleTest extends FeatureTestCase
{
    public function testPassWithSingleValueAsString(): void
    {
        $case = $this->createCase();

        $existsRule = new ExistsRule(EloquentCase::class, 'uuid');
        $result = $existsRule->passes('foo', $case->uuid);

        $this->assertTrue($result);
    }

    public function testPassWithSingleValueAsArray(): void
    {
        $case = $this->createCase();

        $existsRule = new ExistsRule(EloquentCase::class, 'uuid');
        $result = $existsRule->passes('foo', [$case->uuid]);

        $this->assertTrue($result);
    }

    public function testPassWithMultipleValues(): void
    {
        $case1 = $this->createCase();
        $case2 = $this->createCase();

        $existsRule = new ExistsRule(EloquentCase::class, 'uuid');
        $result = $existsRule->passes('foo', [$case1->uuid, $case2->uuid]);

        $this->assertTrue($result);
    }

    public function testFailWithMultipleValuesOneInvalid(): void
    {
        $case1 = $this->createCase();
        $case2 = $this->createCase();

        $existsRule = new ExistsRule(EloquentCase::class, 'uuid');
        $result = $existsRule->passes('foo', [$case1->uuid, $case2->uuid, 'foo']);

        $this->assertFalse($result);
    }

    public function testFailBecauseValueNotFoundInDatabase(): void
    {
        $existsRule = new ExistsRule(EloquentCase::class, 'uuid');
        $result = $existsRule->passes('foo', 'bar');

        $this->assertFalse($result);
    }

    public function testFailBecauseNoClassNotExists(): void
    {
        $existsRule = new ExistsRule('foo', 'uuid');
        $result = $existsRule->passes('foo', 'bar');

        $this->assertFalse($result);
    }

    public function testFailBecauseNoValidEloquentModel(): void
    {
        $existsRule = new ExistsRule(stdClass::class, 'uuid');
        $result = $existsRule->passes('foo', 'bar');

        $this->assertFalse($result);
    }

    public function testFailBecauseNonExistingColumn(): void
    {
        $existsRule = new ExistsRule(EloquentCase::class, 'foo');
        $result = $existsRule->passes('foo', 'bar');

        $this->assertFalse($result);
    }
}

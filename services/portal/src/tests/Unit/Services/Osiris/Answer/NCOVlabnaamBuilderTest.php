<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVlabnaamBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVlabnaamBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVlabnaamBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testSource(): void
    {
        $source = $this->faker->company();
        $case = $this->createCase($source);

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVlabnaam', $source));
    }

    public function testItDoesNotSendCoronitAsSource(): void
    {
        $source = TestResultSource::coronit();
        $case = $this->createCase($source->value);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(string $source): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();

        $case->created_at = CarbonImmutable::now();
        $case->test = new Test();
        $case->test->source = $source;

        return $case;
    }
}

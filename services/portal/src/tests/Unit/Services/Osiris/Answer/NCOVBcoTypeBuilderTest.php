<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV1;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVBcoTypeBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVBcoTypeBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVBcoTypeBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testBCOTypeMappings(): void
    {
        $unknownCount = 0;

        foreach (BCOType::all() as $bcoType) {
            $case = $this->createCaseV3($bcoType);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1, 'No valid BCO type mapping for ' . $bcoType->label);
            $this->assertEquals('NCOVBcoType', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);

            $unknownCount += $answers[0]->value === '4' ? 1 : 0;
        }

        // should only map 1 value to unknown
        $this->assertEquals(1, $unknownCount);
    }

    public function testNullBCOTypeMapping(): void
    {
        $case = $this->createCaseV3(null);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVBcoType', '4'));
    }

    public function testV1Yes(): void
    {
        $case = $this->createCaseV1(YesNoUnknown::yes());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVBcoType', '2'));
    }

    public function testV1No(): void
    {
        $case = $this->createCaseV1(YesNoUnknown::no());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVBcoType', '4'));
    }

    private function createCaseV3(?BCOType $bcoType): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV3);
        $case->createdAt = CarbonImmutable::now();
        $case->extensiveContactTracing->receivesExtensiveContactTracing = $bcoType;
        return $case;
    }

    private function createCaseV1(?YesNoUnknown $receivesExtensiveContactTracing): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(1)->newInstance();
        assert($case instanceof CovidCaseV1);
        $case->createdAt = CarbonImmutable::now();
        $case->extensiveContactTracing->receivesExtensiveContactTracing = $receivesExtensiveContactTracing;
        return $case;
    }
}

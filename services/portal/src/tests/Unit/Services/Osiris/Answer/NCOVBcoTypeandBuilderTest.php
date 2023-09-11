<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV2UpTo4;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVBcoTypeandBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOType;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVBcoTypeandBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVBcoTypeandBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testDescriptionSingleLine(): void
    {
        $case = $this->createCase(BCOType::other(), 'test');
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVBcoTypeand', 'test'));
    }

    public function testDescriptionMultiLine(): void
    {
        $case = $this->createCase(BCOType::other(), "a\nb");
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVBcoTypeand', "a\nb"));
    }

    public function testDescriptionNull(): void
    {
        $case = $this->createCase(BCOType::other(), null);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testBCOTypeNull(): void
    {
        $case = $this->createCase(null, 'test');
        $this->answersForCase($case)->assertEmpty();
    }

    public function testBCOTypeNotOther(): void
    {
        $case = $this->createCase(BCOType::extensive(), 'test');
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?BCOType $bcoType, ?string $description): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV2UpTo4);
        $case->created_at = CarbonImmutable::now();
        $case->extensiveContactTracing->receivesExtensiveContactTracing = $bcoType;
        $case->extensiveContactTracing->otherDescription = $description;
        return $case;
    }
}

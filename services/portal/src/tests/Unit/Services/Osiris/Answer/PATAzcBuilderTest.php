<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\PATAzcBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(PATAzcBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class PATAzcBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testResultYesWithSingleEnv(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), [ContextCategory::asielzoekerscentrum()]);
        $this->answersForCase($case)->assertAnswer(new Answer('PATAzc', 'J'));
    }

    public function testResultYesWithMultipleEnvs(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), [ContextCategory::asielzoekerscentrum(), ContextCategory::bruiloft()]);
        $this->answersForCase($case)->assertAnswer(new Answer('PATAzc', 'J'));
    }

    public function testResultNo(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), [ContextCategory::bouw()]);
        $this->answersForCase($case)->assertAnswer(new Answer('PATAzc', 'N'));
    }

    public function testEmptyLikelySourceEnvironments(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), []);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testHasLikelySourceEnvironmentsNo(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), [ContextCategory::asielzoekerscentrum()]);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testV5ShouldLeaveSourceEnvironmentsEmpty(): void
    {
        $case = EloquentCase::getSchema()->getVersion(5)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $this->answersForCase($case)->assertEmpty();
    }

    /**
     * @param array<ContextCategory>|null $envs
     */
    private function createCase(?YesNoUnknown $hasLikelySourceEnvironments = null, ?array $envs = null): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->sourceEnvironments->hasLikelySourceEnvironments = $hasLikelySourceEnvironments;
        $case->sourceEnvironments->likelySourceEnvironments = $envs;
        return $case;
    }
}

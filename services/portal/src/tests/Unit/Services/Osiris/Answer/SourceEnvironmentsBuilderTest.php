<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Models\Versions\CovidCase\CovidCaseV5;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\SourceEnvironmentsBuilder;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function array_slice;
use function array_unique;
use function assert;
use function count;

#[Builder(SourceEnvironmentsBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class SourceEnvironmentsBuilderTest extends TestCase
{
    use AssertAnswers;

    private const CASE_VERSION = 3; // NOTE: you can't change this without changing some assertions below as well!

    /**
     * Make sure that for every source environment there is an explicit mapping to an Osiris code (even though sometimes other).
     */
    public function testSourceEnvironmentsMappings(): void
    {
        $values = [];
        foreach (ContextCategory::all() as $env) {
            $case = $this->createCase(YesNoUnknown::yes(), [$env]);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1, 'No valid source environment mapping for ' . $env->label);
            $this->assertEquals('NCOVSetting1', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);
            $values[] = $answers[0]->value;
        }

        // currently for each possible value there is a unique mapping, when this changes this check should be removed
        $values = array_unique($values);
        $this->assertEquals(count(ContextCategory::all()), count($values));
    }

    public static function sourceEnvironmentsProvider(): Generator
    {
        yield 'No likely source environments' => [
            YesNoUnknown::no(),
            [],
            [],
        ];

        yield 'No likely source environments, but some environments supplied' => [
            YesNoUnknown::no(),
            [ContextCategory::accomodatieBinnenland(), ContextCategory::buitenland()],
            [],
        ];

        yield 'Source environments unknown' => [
            YesNoUnknown::unknown(),
            [],
            [],
        ];

        yield 'Source environments unknown (null)' => [
            null,
            [],
            [],
        ];

        yield 'Single source environment' => [
            YesNoUnknown::yes(),
            [ContextCategory::zwembad()],
            [
                new Answer('NCOVSetting1', '122'),
            ],
        ];

        yield 'Multiple source environments' => [
            YesNoUnknown::yes(),
            [
                ContextCategory::zwembad(),
                ContextCategory::buitenland(),
                ContextCategory::sport(),
            ],
            [
                new Answer('NCOVSetting1', '122'),
                new Answer('NCOVSetting2', '134'),
                new Answer('NCOVSetting3', '132'),
            ],
        ];
    }

    /**
     * @param array<ContextCategory> $envs
     * @param array<Answer> $expectedAnswers
     */
    #[DataProvider('sourceEnvironmentsProvider')]
    public function testSourceEnvironments(?YesNoUnknown $hasLikelySourceEnvironments, array $envs, array $expectedAnswers): void
    {
        $case = $this->createCase($hasLikelySourceEnvironments, $envs);
        $this->answersForCase($case)->assertAnswers($expectedAnswers);
    }

    public function testMaxSourceEnvironments(): void
    {
        $envs = array_slice(ContextCategory::all(), 0, 10);
        $case = $this->createCase(YesNoUnknown::yes(), $envs);
        $this->answersForCase($case)->assertCount(3); // only NCOVSettingX
    }

    public function testV5EmptySourceEnvironments(): void
    {
        $case = EloquentCase::getSchema()->getVersion(5)->newInstance();
        assert($case instanceof CovidCaseV5);
        $case->createdAt = CarbonImmutable::now();
        $this->answersForCase($case)->assertEmpty();
    }

    /**
     * @param array<ContextCategory>|null $envs
     */
    private function createCase(?YesNoUnknown $hasLikelySourceEnvironments = null, ?array $envs = null): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(self::CASE_VERSION)->newInstance();
        assert($case instanceof CovidCaseV3);
        $case->createdAt = CarbonImmutable::now();
        $case->sourceEnvironments->hasLikelySourceEnvironments = $hasLikelySourceEnvironments;
        $case->sourceEnvironments->likelySourceEnvironments = $envs;
        return $case;
    }
}

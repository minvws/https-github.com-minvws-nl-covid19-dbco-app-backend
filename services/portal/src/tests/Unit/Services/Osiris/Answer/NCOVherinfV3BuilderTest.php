<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVherinfV3Builder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVherinfV3Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVherinfV3BuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('isReinfectionDataProvider')]
    public function testIsReinfection(int $caseVersion, ?YesNoUnknown $isRefinfection, string $expectedResult): void
    {
        $case = $this->createCase($caseVersion, $isRefinfection);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVherinfV3', $expectedResult));
    }

    public static function isReinfectionDataProvider(): array
    {
        return [
            '3, yes' => [3, YesNoUnknown::yes(), 'J'],
            '3, no' => [3, YesNoUnknown::no(), 'N'],
            '3, unknown' => [3, YesNoUnknown::unknown(), 'Onb'],
            '3, null' => [3, null, 'Onb'],

            '4, yes' => [4, YesNoUnknown::yes(), 'J'],
            '4, no' => [4, YesNoUnknown::no(), 'N'],
            '4, unknown' => [4, YesNoUnknown::unknown(), 'Onb'],
            '4, null' => [4, null, 'Onb'],
        ];
    }

    private function createCase(
        int $caseVersion,
        ?YesNoUnknown $isReinfection,
    ): EloquentCase {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        $case->createdAt = CarbonImmutable::now();

        $case->test->isReinfection = $isReinfection;

        return $case;
    }
}

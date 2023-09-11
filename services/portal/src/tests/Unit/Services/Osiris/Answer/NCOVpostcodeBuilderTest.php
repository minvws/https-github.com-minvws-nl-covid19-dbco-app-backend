<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Models\Versions\CovidCase\IndexAddress\IndexAddressV1;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVpostcodeBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\NoBsnOrAddressReason;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVpostcodeBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVpostcodeBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('postCodeDataProvider')]
    public function testPostcode(
        int $caseVersion,
        bool|array|null $hasNoBsnOrAddress,
        ?string $postalCode,
        string $expectedResult,
    ): void
    {
        $case = $this->createCase($caseVersion, $hasNoBsnOrAddress, $postalCode);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpostcode', $expectedResult));
    }

    public static function postCodeDataProvider(): array
    {
        return [
            '3, false, 1234AB' => [3, false, '1234AB', '1234AB'],
            '3, false, 1234ab' => [3, false, '1234ab', '1234AB'],
            '3, false, invalid' => [3, false, 'invalid', '008'],
            '3, false, null' => [3, false, null, '008'],
            '3, true, 1234AB' => [3, true, '1234AB', '008'],
            '3, null, null' => [3, null, null, '008'],

            '4, false, 1234AB' => [4, false, '1234AB', '1234AB'],
            '4, false, 1234ab' => [4, false, '1234ab', '1234AB'],
            '4, false, invalid' => [4, false, 'invalid', '008'],
            '4, false, null' => [4, false, null, '008'],
            '4, true, 1234AB' => [4, true, '1234AB', '008'],
            '4, null, null' => [4, null, null, '008'],

            '5, false, 1234AB' => [5, [], '1234AB', '1234AB'],
            '5, false, 1234ab' => [5, [], '1234ab', '1234AB'],
            '5, false, invalid' => [5, [], 'invalid', '008'],
            '5, false, null' => [5, [], null, '008'],
            '5, true, 1234AB' => [5, [NoBsnOrAddressReason::homeless()], '1234AB', '001'],
            '5, null, null' => [5, null, null, '008'],
        ];
    }

    #[DataProvider('noAddressDataProvider')]
    public function testHasNoIndexAddress(int $caseVersion): void
    {
        /** @var CovidCaseV3 $case */
        $case = CovidCaseV3::getSchema()->getVersion($caseVersion)->newInstance();
        $case->index->address = null;

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpostcode', '008'));
    }

    public function testItSendsHomelessWhenHomeless(): void
    {
        $case = $this->createCase(5, [], '1234AB');
        $case->index->hasNoBsnOrAddress = [NoBsnOrAddressReason::homeless()];

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpostcode', '001'));
    }

    public function testItSendsHomelessWhenHomelessAndForeign(): void
    {
        $case = $this->createCase(5, [], '1234AB');
        $case->index->hasNoBsnOrAddress = [NoBsnOrAddressReason::foreignPasserby(), NoBsnOrAddressReason::homeless()];

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpostcode', '001'));
    }

    public function testItSendsForeignWhenForeign(): void
    {
        $case = $this->createCase(5, [], '1234AB');
        $case->index->hasNoBsnOrAddress = [NoBsnOrAddressReason::foreignPasserby()];

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpostcode', '009'));
    }

    public function testItSendsUnknownWhenNotForeignOrHomeless(): void
    {
        $case = $this->createCase(5, [], '1234AB');
        $case->index->hasNoBsnOrAddress = [NoBsnOrAddressReason::noCooperation()];

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpostcode', '008'));
    }

    public static function noAddressDataProvider(): array
    {
        return [
            '3' => [3],
            '4' => [4],
        ];
    }

    private function createCase(int $caseVersion, bool|array|null $hasNoBsnOrAddress, ?string $postalCode): EloquentCase
    {
        /** @var IndexAddressV1 $indexAddress */
        $indexAddress = IndexAddressV1::getSchema()->getVersion(1)->newInstance();
        $indexAddress->postalCode = $postalCode;

        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        $case->createdAt = CarbonImmutable::now();

        $case->index->hasNoBsnOrAddress = $hasNoBsnOrAddress;
        $case->index->address = $indexAddress;

        return $case;
    }
}

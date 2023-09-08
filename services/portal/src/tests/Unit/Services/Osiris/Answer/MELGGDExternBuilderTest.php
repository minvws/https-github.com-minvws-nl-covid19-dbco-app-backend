<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Communication\CommunicationV3;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\MELGGDExternBuilder;
use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(MELGGDExternBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class MELGGDExternBuilderTest extends TestCase
{
    use AssertAnswers;

    public static function remarksProvider(): Generator
    {
        yield "simple text" => ["simple text", "simple text"];
        yield "multi-line text" => ["multi\nline\ntext", "multi\nline\ntext"];
    }

    public function testAnswersEmptyWhenRemarksRivmIsEmptyString(): void
    {
        $case = $this->createCase('');
        $this->answersForCase($case)->assertEmpty();
    }

    public function testAnswersEmptyWhenRemarksRivmIsWhitespaceOnly(): void
    {
        //single whitespace
        $case = $this->createCase(' ');
        $this->answersForCase($case)->assertEmpty();
        //multiple whitespace
        $case = $this->createCase('                                                         ');
        $this->answersForCase($case)->assertEmpty();
    }

    #[DataProvider('remarksProvider')]
    public function testRemarks(string $remarks, string $expectedValue): void
    {
        $case = $this->createCase($remarks);
        $this->answersForCase($case)->assertAnswer(new Answer('MELGGDExtern', $expectedValue));
    }

    private function createCase(string $remarks): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(5)->newInstance();
        assert($case instanceof EloquentCase);
        assert($case->communication instanceof CommunicationV3);
        $case->createdAt = CarbonImmutable::now();
        $case->communication->remarksRivm = $remarks;
        return $case;
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV2Up;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVContactInv3Builder;
use Generator;
use MinVWS\DBCO\Enum\Models\BCOType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVContactInv3Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVContactInv3BuilderTest extends FeatureTestCase
{
    use AssertAnswers;

    public static function contactsAndSourcesProvider(): Generator
    {
        yield "0 contacts, 0 sources" => [0, 0, 0, '5']; // not started
        yield "1 contact, 0 sources" => [1, 0, 0, '1']; // started
        yield "2 contacts, 0 sources" => [2, 0, 0, '1'];
        yield "0 contacts, 1 positive source, 0 symptomatic sources" => [0, 1, 0, '1'];
        yield "1 contact, 1 positive source, 0 symptomatic sources" => [1, 1, 0, '1'];
        yield "0 contacts, 2 positive sources, 0 symptomatic sources" => [0, 2, 0, '1'];
        yield "0 contacts, 0 positive sources, 1 symptomatic source" => [0, 0, 1, '1'];
        yield "1 contact, 0 positive sources, 1 symptomatic source" => [1, 0, 1, '1'];
        yield "0 contacts, 0 positive sources, 2 symptomatic sources" => [0, 0, 2, '1'];
        yield "0 contacts, 1 positive source, 1 symptomatic source" => [0, 1, 1, '1'];
        yield "0 contacts, 2 positive source, 3 symptomatic source" => [0, 2, 3, '1'];
        yield "1 contact, 1 positive source, 1 symptomatic source" => [1, 1, 1, '1'];
        yield "4 contacts, 2 positive source, 3 symptomatic source" => [4, 2, 3, '1'];
    }

    #[DataProvider('contactsAndSourcesProvider')]
    public function testBCOTypeExtensive(int $contacts, int $positiveSources, int $symptomaticSources, string $expectedValue): void
    {
        $case = $this->createCaseWithBCOTypeAndTasks(BCOType::extensive(), $contacts, $positiveSources, $symptomaticSources);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVContactInv3', $expectedValue));
    }

    #[DataProvider('contactsAndSourcesProvider')]
    public function testBCOTypeNull(int $contacts, int $positiveSources, int $symptomaticSources): void
    {
        $case = $this->createCaseWithBCOTypeAndTasks(null, $contacts, $positiveSources, $symptomaticSources);
        $this->answersForCase($case)->assertEmpty();
    }

    #[DataProvider('contactsAndSourcesProvider')]
    public function testBCOTypeNotExtensive(int $contacts, int $positiveSources, int $symptomaticSources): void
    {
        $case = $this->createCaseWithBCOTypeAndTasks(BCOType::standard(), $contacts, $positiveSources, $symptomaticSources);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCaseWithBCOTypeAndTasks(?BCOType $bcoType, int $contacts, int $positiveSources, int $symptomaticSources): EloquentCase
    {
        $case = $this->createCaseWithTasks($contacts, $positiveSources, $symptomaticSources);
        assert($case instanceof CovidCaseV2Up);
        $case->extensiveContactTracing->receivesExtensiveContactTracing = $bcoType;
        $case->save();
        return $case;
    }
}

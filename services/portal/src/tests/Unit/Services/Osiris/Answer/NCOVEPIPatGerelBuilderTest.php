<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVEPIPatGerelBuilder;
use Generator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\ModelCreator;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVEPIPatGerelBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVEPIPatGerelBuilderTest extends TestCase
{
    use AssertAnswers;
    use DatabaseTransactions;
    use ModelCreator;

    public static function contactsAndSourcesProvider(): Generator
    {
        yield "0 contacts, 0 sources" => [0, 0, 0, 'Onb'];
        yield "1 contact, 0 sources" => [1, 0, 0, 'N'];
        yield "2 contacts, 0 sources" => [2, 0, 0, 'N'];
        yield "0 contacts, 1 positive source, 0 symptomatic sources" => [0, 1, 0, 'J'];
        yield "1 contact, 1 positive source, 0 symptomatic sources" => [1, 1, 0, 'J'];
        yield "0 contacts, 2 positive sources, 0 symptomatic sources" => [0, 2, 0, 'J'];
        yield "0 contacts, 0 positive sources, 1 symptomatic source" => [0, 0, 1, 'J'];
        yield "1 contact, 0 positive sources, 1 symptomatic source" => [1, 0, 1, 'J'];
        yield "0 contacts, 0 positive sources, 2 symptomatic sources" => [0, 0, 2, 'J'];
        yield "0 contacts, 1 positive source, 1 symptomatic source" => [0, 1, 1, 'J'];
        yield "0 contacts, 2 positive source, 3 symptomatic source" => [0, 2, 3, 'J'];
        yield "1 contact, 1 positive source, 1 symptomatic source" => [1, 1, 1, 'J'];
        yield "4 contacts, 2 positive source, 3 symptomatic source" => [4, 2, 3, 'J'];
    }

    #[DataProvider('contactsAndSourcesProvider')]
    public function testContactsAndSources(int $contacts, int $positiveSources, int $symptomaticSources, string $expectedValue): void
    {
        $case = $this->createCaseWithTasks($contacts, $positiveSources, $symptomaticSources);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVEPIPatGerel', $expectedValue));
    }
}

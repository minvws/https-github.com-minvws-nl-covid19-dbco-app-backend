<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Services\Osiris\Answer\Utils;
use DateTime;
use DateTimeInterface;
use Generator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\ModelCreator;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-answer')]
class UtilsTest extends TestCase
{
    use DatabaseTransactions;
    use ModelCreator;

    public static function dateProvider(): Generator
    {
        yield "10-05-2022" => [new DateTime("2022-05-10"), "10-05-2022"];
        yield "05-10-2022" => [new DateTime("2022-10-05"), "05-10-2022"];
        yield "null" => [null, null];
    }

    #[DataProvider('dateProvider')]
    public function testFormatDate(?DateTimeInterface $date, ?string $formattedDate): void
    {
        $this->assertEquals($formattedDate, Utils::formatDate($date));
    }

    public function testMapYesNoUnknown(): void
    {
        $this->assertEquals('J', Utils::mapYesNoUnknown(YesNoUnknown::yes()));
        $this->assertEquals('N', Utils::mapYesNoUnknown(YesNoUnknown::no()));
        $this->assertEquals('Onb', Utils::mapYesNoUnknown(YesNoUnknown::unknown()));
        $this->assertEquals(null, Utils::mapYesNoUnknown(null));
    }

    public function testGetContactsAndSources(): void
    {
        $case = $this->createCaseWithTasks(5, 3, 4);
        $this->assertCount(5, Utils::getContacts($case));
        $this->assertCount(7, Utils::getSources($case));
        $this->assertCount(12, Utils::getContactsAndSources($case));
    }
}

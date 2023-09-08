<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Models\CovidCase\Contacts;
use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Contacts\ContactsV1;
use App\Models\Versions\CovidCase\Contacts\ContactsV2;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('fragment')]
final class ContactsTest extends TestCase
{
    public function testContactsVersion1HasEstimatedCategory3Contacts(): void
    {
        $estimatedCategory3Contacts = $this->faker->randomDigit();

        /** @var ContactsV1 $contacts */
        $contacts = Contacts::newInstanceWithVersion(
            1,
            static function (ContactsV1 $contacts) use ($estimatedCategory3Contacts): void {
                $contacts->estimatedCategory3Contacts = $estimatedCategory3Contacts;
            },
        );

        $this->assertEquals($estimatedCategory3Contacts, $contacts->estimatedCategory3Contacts);
    }

    public function testContactsVersion2HasEstimatedCategory3Contacts(): void
    {
        $estimatedCategory3Contacts = $this->faker->randomDigit();

        /** @var ContactsV2 $contacts */
        $contacts = Contacts::newInstanceWithVersion(
            2,
            static function (ContactsV2 $contacts) use ($estimatedCategory3Contacts): void {
                $contacts->estimatedCategory3Contacts = $estimatedCategory3Contacts;
            },
        );

        $this->assertEquals($estimatedCategory3Contacts, $contacts->estimatedCategory3Contacts);
    }

    #[DataProvider('caseWithContactsDataProvider')]
    public function testCaseWithContacts(int $version, int $expectedContactsVersion): void
    {
        $case = EloquentCase::newInstanceWithVersion($version);

        $this->assertEquals($case->contacts->getSchemaVersion()->getVersion(), $expectedContactsVersion);
    }

    public static function caseWithContactsDataProvider(): array
    {
        return [
            [1, 1],
            [2, 2],
            [3, 2],
            [4, 2],
        ];
    }
}

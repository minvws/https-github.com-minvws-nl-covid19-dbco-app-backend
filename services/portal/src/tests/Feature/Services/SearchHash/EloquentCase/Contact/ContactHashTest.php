<?php

declare(strict_types=1);

namespace Tests\Feature\Services\SearchHash\EloquentCase\Contact;

use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Index;
use App\Models\Eloquent\EloquentCase;
use App\Services\SearchHash\EloquentCase\Contact\ContactHash;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('search-hash')]
class ContactHashTest extends FeatureTestCase
{
    public function testFromCaseWithValidData(): void
    {
        $dateOfBirth = $this->faker->dateTimeBetween();
        $phone = $this->faker->phoneNumber();
        $formattedPhone = PhoneFormatter::format($phone);

        $case = EloquentCase::newInstanceWithVersion(1, function (EloquentCase $case): void {
            $case->created_at = $this->faker->dateTimeBetween();
            $case->updated_at = clone $case->created_at;
        });
        $case->index = Index::newInstanceWithVersion(1, static function (Index $index) use ($dateOfBirth): void {
            $index->dateOfBirth = $dateOfBirth;
        });
        $case->contact = Contact::newInstanceWithVersion(1, static function (Contact $contact) use ($phone): void {
            $contact->phone = $phone;
        });

        $expectedContactHash = new ContactHash($dateOfBirth, $formattedPhone);
        $this->assertEquals($expectedContactHash, ContactHash::fromCase($case));
    }

    public function testFromCaseWithEmptyIndexAndContact(): void
    {
        $case = EloquentCase::newInstanceWithVersion(1, function (EloquentCase $case): void {
            $case->created_at = $this->faker->dateTimeBetween();
            $case->updated_at = clone $case->created_at;
        });
        $case->index = Index::newInstanceWithVersion(1);
        $case->contact = Contact::newInstanceWithVersion(1);

        $expectedContactHash = new ContactHash(null, null);
        $this->assertEquals($expectedContactHash, ContactHash::fromCase($case));
    }

    public function testFromCaseWithNonExistingIndexAndContact(): void
    {
        $case = EloquentCase::newInstanceWithVersion(1, function (EloquentCase $case): void {
            $case->created_at = $this->faker->dateTimeBetween();
            $case->updated_at = clone $case->created_at;
        });

        $expectedContactHash = new ContactHash(null, null);
        $this->assertEquals($expectedContactHash, ContactHash::fromCase($case));
    }
}

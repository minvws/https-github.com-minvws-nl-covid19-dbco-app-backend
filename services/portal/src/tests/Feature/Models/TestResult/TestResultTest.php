<?php

declare(strict_types=1);

namespace Tests\Feature\Models\TestResult;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Person;
use App\Models\Eloquent\TestResult;
use App\Models\Eloquent\TestResultRaw;
use App\Models\TestResult\General;
use Carbon\CarbonImmutable;
use DateTime;
use DomainException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use MinVWS\DBCO\Enum\Models\TestResultType;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;
use Tests\Traits\MocksEncryptionHelper;

use function is_null;
use function rand;

#[Group('test-result')]
final class TestResultTest extends FeatureTestCase
{
    use MocksEncryptionHelper;

    private function makePerson(): Person
    {
        $person = Person::newInstanceWithVersion(1);
        $person->date_of_birth = CarbonImmutable::parse('70 years ago');
        $person->nameAndAddress->firstname = 'John';
        $person->nameAndAddress->lastname = 'Doe';
        $person->nameAndAddress->address->town = 'Amsterdam';
        $person->contactDetails->phone = '0612345678';
        return $person;
    }

    private function makeTestResult(
        EloquentOrganisation $organisation,
        ?Person $person,
        ?EloquentCase $covidCase = null,
        bool $withGeneralFragment = true,
    ): TestResult {
        $testResult = TestResult::newInstanceWithVersion(1);
        $testResult->messageId = $this->faker->uuid();
        $testResult->organisation()->associate($organisation);
        if ($person) {
            $testResult->person()->associate($person);
        }
        $testResult->type = TestResultType::lab();
        $testResult->source = TestResultSource::coronit();
        $testResult->sourceId = Uuid::uuid4();
        $testResult->monsterNumber = rand(100_000_000, 999_999_999);
        $testResult->dateOfTest = new DateTime('yesterday');
        $testResult->receivedAt = new DateTime('now');
        if ($covidCase) {
            $testResult->covidCase()->associate($covidCase);
        }
        if ($withGeneralFragment) {
            $testResult->general->testLocation = 'Amsterdam Arena';
        }
        return $testResult;
    }

    public function testCreate(): void
    {
        $organisation = $this->createOrganisation();
        $person = $this->makePerson();
        $this->assertTrue($person->save());
        // make sure the fragments are saved as well
        $this->assertTrue($person->nameAndAddress->exists);
        $this->assertTrue($person->contactDetails->exists);

        $testResult = $this->makeTestResult($organisation, $person);
        $this->assertTrue($testResult->save());
        // make sure the fragment is saved as well
        $this->assertTrue($testResult->general->exists);

        /** @var TestResultRaw $rawTestResult */
        $rawTestResult = TestResultRaw::newInstanceWithVersion(1);
        $rawTestResult->testResult()->associate($testResult);
        $rawTestResult->data = '<TestResult></TestResult>';
        $testResult->raw()->save($rawTestResult);
        $this->assertTrue($rawTestResult->exists);
        $testResult->refresh();
        $this->assertFalse(is_null($testResult->raw));
        $this->assertEquals('<TestResult></TestResult>', $testResult->raw->data);
    }

    public function testCovidCaseRelationship(): void
    {
        $organisation = $this->createOrganisation();
        $person = $this->makePerson();
        $this->assertTrue($person->save());

        $covidCase = $this->createCase();
        $this->assertTrue($covidCase->save());

        $testResult = $this->makeTestResult($organisation, $person, $covidCase);
        $testResult->save();

        $testResult2 = $this->makeTestResult($organisation, $person, $covidCase);
        $testResult2->save();

        $this->assertSame($covidCase, $testResult->covidCase);
        $this->assertSame($covidCase, $testResult2->covidCase);
    }

    public function testLoadingWithFragments(): void
    {
        $organisation = $this->createOrganisation();
        $person = $this->makePerson();
        $person->save();
        $testResult = $this->makeTestResult($organisation, $person);
        $testResult->save();

        /** @var TestResult $loadedTestResult */
        $loadedTestResult = TestResult::query()->with(['general', 'person.nameAndAddress', 'person.contactDetails'])->find($testResult->id);
        $this->assertNotNull($loadedTestResult);
        $this->assertEquals($testResult->uuid, $loadedTestResult->uuid);
        $this->assertTrue($loadedTestResult->relationLoaded('general'));
        $this->assertTrue($loadedTestResult->relationLoaded('person'));
        $this->assertTrue($loadedTestResult->person->relationLoaded('nameAndAddress'));
        $this->assertTrue($loadedTestResult->person->relationLoaded('contactDetails'));
        $this->assertEquals($testResult->general->testLocation, $loadedTestResult->general->testLocation);
        $this->assertEquals($testResult->person->nameAndAddress->firstname, $loadedTestResult->person->nameAndAddress->firstname);
    }

    public function testLazyLoadingOfFragments(): void
    {
        $organisation = $this->createOrganisation();
        $person = $this->makePerson();
        $person->save();
        $testResult = $this->makeTestResult($organisation, $person);
        $testResult->save();

        /** @var TestResult $loadedTestResult */
        $loadedTestResult = TestResult::query()->find($testResult->id);
        $this->assertNotNull($loadedTestResult);
        $this->assertEquals($testResult->uuid, $loadedTestResult->uuid);
        $this->assertFalse($loadedTestResult->relationLoaded('general'));
        $this->assertFalse($loadedTestResult->relationLoaded('person'));
        $this->assertFalse($loadedTestResult->relationLoaded('covidCase'));
        $this->assertFalse($loadedTestResult->person->relationLoaded('nameAndAddress'));
        $this->assertFalse($loadedTestResult->person->relationLoaded('contactDetails'));
        $this->assertEquals($testResult->general->testLocation, $loadedTestResult->general->testLocation);
        $this->assertEquals($testResult->person->nameAndAddress->firstname, $loadedTestResult->person->nameAndAddress->firstname);
        $this->assertTrue($loadedTestResult->relationLoaded('general'));
        $this->assertTrue($loadedTestResult->relationLoaded('person'));
        $this->assertTrue($loadedTestResult->person->relationLoaded('nameAndAddress'));
        $this->assertFalse($loadedTestResult->person->relationLoaded('contactDetails'));
    }

    public function testFragmentAssignment(): void
    {
        $organisation = $this->createOrganisation();
        $person = $this->makePerson();
        $person->save();
        $testResult = $this->makeTestResult($organisation, $person);
        $testResult->save();

        $oldGeneral = $testResult->general;
        $this->assertTrue($oldGeneral->exists);

        /** @var General $newGeneral */
        $newGeneral = $testResult->getSchemaVersion()->getExpectedField('general')->newInstance();
        $newGeneral->testLocation = 'Hatseflats';
        $this->assertFalse($newGeneral->exists);

        $testResult->general = $newGeneral;

        $this->assertSame($newGeneral, $testResult->general);
        $this->assertFalse($newGeneral->exists);
        $this->assertTrue($oldGeneral->exists);

        $testResult->save();

        $this->assertTrue($newGeneral->exists);
        $this->assertFalse($oldGeneral->exists);
    }

    public function testFragmentRelationSave(): void
    {
        $organisation = $this->createOrganisation();
        $person = $this->makePerson();
        $person->save();

        $testResult = $this->makeTestResult($organisation, $person, null, false);
        $testResult->save();

        $this->assertFalse($testResult->general()->exists());

        /** @var General $general */
        $general = $testResult->getSchemaVersion()->getExpectedField('general')->newInstance();
        $general->testLocation = 'Hatseflats';
        $testResult->general()->save($general);
    }

    public function testGuardAgainstCustomTypeWithoutAdditionalData(): void
    {
        $organisation = $this->createOrganisation();
        $testResult = $this->makeTestResult($organisation, null, null, false);

        $this->expectException(DomainException::class);

        $testResult->setTypeOfTest(TestResultTypeOfTest::custom(), null);
    }

    public function testGuardAgainstAdditionalDataWithNonCustomType(): void
    {
        $organisation = $this->createOrganisation();
        $testResult = $this->makeTestResult($organisation, null, null, false);

        $this->expectException(DomainException::class);

        $testResult->setTypeOfTest(TestResultTypeOfTest::unknown(), 'Custom Type');
    }

    public function testDateOfBirthEncryptedCasting(): void
    {
        $dateOfBirth = CarbonImmutable::instance($this->faker->dateTime);

        $person = $this->createPerson(['date_of_birth' => $dateOfBirth]);
        $person->refresh();

        $this->assertTrue($dateOfBirth->equalTo($person->date_of_birth));
    }

    public function testDateOfBirthAndDateOfBirthEncryptedValue(): void
    {
        $person = $this->makePerson();
        $person->save();
        $person->refresh();

        $encryptionHelper = $this->app->get(EncryptionHelper::class);
        $dateOfBirthEncrypted = $encryptionHelper->sealStoreValue(
            $person->date_of_birth_encrypted->serialize(),
            StorageTerm::short(),
            $person->createdAt,
        );

        $this->assertDatabaseHas('person', [
            'uuid' => Uuid::fromString($person->uuid)->getBytes(),
            'date_of_birth_encrypted' => $dateOfBirthEncrypted,
        ]);
    }
}

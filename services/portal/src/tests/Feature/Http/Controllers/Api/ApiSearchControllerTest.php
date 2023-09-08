<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Helpers\SearchableHash;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Index;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use App\Models\Task\General;
use App\Models\Task\PersonalDetails;
use App\Models\Versions\CovidCase\General\GeneralV1;
use Carbon\CarbonImmutable;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function app;
use function sprintf;

final class ApiSearchControllerTest extends FeatureTestCase
{
    private EloquentUser $user;
    private SearchableHash $searchableHash;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser([], 'compliance');
        $this->searchableHash = app(SearchableHash::class);
    }

    #[DataProvider('searchByIdentifierProvider')]
    public function testSearchingForCaseByReference(string $identifier): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'identifier' => $identifier,
        ]);

        $response->assertJson([
            'query' => [
                'identifier' => $identifier,
            ],
            'contacts' => [],
            'cases' => [
                [
                    'uuid' => $case->uuid,
                    'number' => 'AB1-123-123',
                    'hpzone_number' => '1234321',
                    'monster_number' => '123A4444444',
                    'dateOfSymptomOnset' => CarbonImmutable::now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public static function searchByIdentifierProvider(): array
    {
        return [
            ['1234321'],
            ['123A4444444'],
            ['AB1-123-123'],
        ];
    }

    public function testSearchingForCaseByCaseUuid(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'caseUuid' => $case->uuid,
        ]);

        $response->assertJson([
            'query' => [
                'caseUuid' => $case->uuid,
            ],
            'contacts' => [],
            'cases' => [
                [
                    'uuid' => $case->uuid,
                    'number' => 'AB1-123-123',
                    'hpzone_number' => '1234321',
                    'monster_number' => '123A4444444',
                    'dateOfSymptomOnset' => CarbonImmutable::now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function testSearchingForCaseByIncorrectCaseUuid(): void
    {
        $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'caseUuid' => '61434af2-4f76-4fe8-9fc2-f218810a6b35',
        ]);

        $response->assertJson([
            'query' => [
                'caseUuid' => '61434af2-4f76-4fe8-9fc2-f218810a6b35',
            ],
            'contacts' => [],
            'cases' => [],
        ]);
    }

    public function testSearchingForCaseByLastnameAndDateOfBirth(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'lastname' => 'Duck',
            'dateOfBirth' => '1994-11-05',
        ]);

        $response->assertJson([
            'query' => [
                'lastname' => 'Duck',
                'dateOfBirth' => '1994-11-05',
            ],
            'contacts' => [],
            'cases' => [
                [
                    'uuid' => $case->uuid,
                    'number' => 'AB1-123-123',
                    'hpzone_number' => '1234321',
                    'monster_number' => '123A4444444',
                    'dateOfSymptomOnset' => CarbonImmutable::now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function testSearchingForCaseByLastnameAndDateOfBirthDifferentFormat(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'lastname' => 'Duck',
            'dateOfBirth' => '1994-11-05',
        ]);

        $response->assertJson([
            'query' => [
                'lastname' => 'Duck',
                'dateOfBirth' => '1994-11-05',
            ],
            'contacts' => [],
            'cases' => [
                [
                    'uuid' => $case->uuid,
                    'number' => 'AB1-123-123',
                    'hpzone_number' => '1234321',
                    'monster_number' => '123A4444444',
                    'dateOfSymptomOnset' => CarbonImmutable::now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function testSearchingForCaseUnknownHpzoneNumber(): void
    {
        $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'identifier' => '9999999',
        ]);

        $response->assertJson([
            'query' => [
                'identifier' => '9999999',
            ],
            'contacts' => [],
            'cases' => [],
        ]);
    }

    public function testSearchingForCaseByLastnameAndEmail(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'lastname' => 'Duck',
            'email' => 'donald@duck.com',
        ]);

        $response->assertJson([
            'query' => [
                'lastname' => 'Duck',
                'email' => 'donald@duck.com',
            ],
            'contacts' => [],
            'cases' => [
                [
                    'uuid' => $case->uuid,
                    'number' => 'AB1-123-123',
                    'hpzone_number' => '1234321',
                    'monster_number' => '123A4444444',
                    'dateOfSymptomOnset' => CarbonImmutable::now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function testSearchingForCaseByLastnameAndPhone(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'lastname' => 'Duck',
            'phone' => '0612345678',
        ]);

        $response->assertJson([
            'query' => [
                'lastname' => 'Duck',
                'phone' => '0612345678',
            ],
            'contacts' => [],
            'cases' => [
                [
                    'uuid' => $case->uuid,
                    'number' => 'AB1-123-123',
                    'hpzone_number' => '1234321',
                    'monster_number' => '123A4444444',
                    'dateOfSymptomOnset' => CarbonImmutable::now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function testSearchingForCaseByLastnameEmailAndDateOfBirth(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'lastname' => 'Duck',
            'email' => 'donald@duck.com',
            'dateOfBirth' => '1994-11-05',
        ]);

        $response->assertJson([
            'query' => [
                'lastname' => 'Duck',
                'email' => 'donald@duck.com',
                'dateOfBirth' => '1994-11-05',
            ],
            'contacts' => [],
            'cases' => [
                [
                    'uuid' => $case->uuid,
                    'number' => 'AB1-123-123',
                    'hpzone_number' => '1234321',
                    'monster_number' => '123A4444444',
                    'dateOfSymptomOnset' => CarbonImmutable::now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function testSearchingForCaseByLastnamePhoneEmailAndDateOfBirth(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'lastname' => 'Duck',
            'phone' => '0612345678',
            'email' => 'donald@duck.com',
            'dateOfBirth' => '1994-11-05',
        ]);

        $response->assertJson([
            'query' => [
                'lastname' => 'Duck',
                'phone' => '0612345678',
                'email' => 'donald@duck.com',
                'dateOfBirth' => '1994-11-05',
            ],
            'contacts' => [],
            'cases' => [
                [
                    'uuid' => $case->uuid,
                    'number' => 'AB1-123-123',
                    'hpzone_number' => '1234321',
                    'monster_number' => '123A4444444',
                    'dateOfSymptomOnset' => CarbonImmutable::now()->format('Y-m-d'),
                ],
            ],
        ]);
    }

    public function testSearchingForCaseByLastnameAndIncorrectPhone(): void
    {
        $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'lastname' => 'Duck',
            'phone' => '0687654321',
        ]);

        $response->assertJson([
            'query' => [
                'lastname' => 'Duck',
                'phone' => '0687654321',
            ],
            'contacts' => [],
            'cases' => [],
        ]);
    }

    public function testSearchingForCaseByEmptyValue(): void
    {
        $this->createCaseWithIndexFragment($this->user);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'identifier' => null,
        ]);

        $response->assertJson([
            'query' => [
                'identifier' => null,
            ],
            'contacts' => [],
            'cases' => [],
        ]);
    }

    public function testSearchingForTaskByUuid(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);
        $task = $this->createTaskForCaseWithFragments($case);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'taskUuid' => $task->uuid,
        ]);

        $response->assertJson([
            'query' => [
                'taskUuid' => $task->uuid,
            ],
            'contacts' => [
                [
                    'uuid' => $task->uuid,
                    'contactDate' => CarbonImmutable::now()->format('Y-m-d'),
                    'category' => null,
                    'index' => [
                        'number' => 'AB1-123-123',
                        'relationship' => null,
                    ],
                ],
            ],
            'cases' => [],
        ]);
    }

    public function testSearchingForTaskByLastnameAndPhone(): void
    {
        $case = $this->createCaseWithIndexFragment($this->user);
        $task = $this->createTaskForCaseWithFragments($case);

        $response = $this->actingAs($this->user)->post('/api/search', [
            'lastname' => 'Duckster',
            'phone' => '0623456781',
        ]);

        $response->assertJson([
            'query' => [
                'lastname' => 'Duckster',
                'phone' => '0623456781',
            ],
            'contacts' => [
                [
                    'uuid' => $task->uuid,
                    'contactDate' => CarbonImmutable::now()->format('Y-m-d'),
                    'category' => null,
                    'index' => [
                        'number' => 'AB1-123-123',
                        'relationship' => null,
                    ],
                ],
            ],
            'cases' => [],
        ]);
    }

    private function createCaseWithIndexFragment(EloquentUser $user): EloquentCase
    {
        $date = new DateTime('1994-11-05');
        $email = 'donald@duck.com';
        $phone = '0612345678';

        $dateOfBirthHash = $this->searchableHash->hashForLastNameAndDateOfBirth('Duck', $date);
        $emailHash = $this->searchableHash->hashForLastNameAndEmail('Duck', $email);
        $phoneHash = $this->searchableHash->hashForLastNameAndPhone('Duck', $phone);

        $contact = Contact::newInstanceWithVersion(1);
        $contact->phone = $phone;
        $contact->email = $email;

        $index = Index::newInstanceWithVersion(1);
        $index->dateOfBirth = $date;
        $index->firstname = 'Donald';
        $index->lastname = 'Duck';

        $general = \App\Models\CovidCase\General::newInstanceWithVersion(GeneralV1::getSchema()->getCurrentVersion()->getVersion());
        $general->reference = 'AB1-123-123';
        $general->hpzoneNumber = '1234321';

        return $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'index' => $index,
            'contact' => $contact,
            'general' => $general,
            'test_monster_number' => '123A4444444',
            'index_bsn_ends_with' => '789',
            'search_date_of_birth' => $dateOfBirthHash,
            'search_email' => $emailHash,
            'search_phone' => $phoneHash,
            'date_of_symptom_onset' => CarbonImmutable::now(),
        ]);
    }

    private function createTaskForCaseWithFragments(EloquentCase $case): EloquentTask
    {
        $dateOfBirth = new DateTime('1994-11-05');
        $email = 'task@duck.com';
        $firstname = 'Dagobert';
        $lastname = 'Duckster';
        $phone = '0623456781';

        $dateOfBirthHash = $this->searchableHash->hashForLastNameAndDateOfBirth($lastname, $dateOfBirth);
        $emailHash = $this->searchableHash->hashForLastNameAndEmail($lastname, $email);
        $phoneHash = $this->searchableHash->hashForLastNameAndPhone($lastname, $phone);

        return $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'general' => General::newInstanceWithVersion(
                1,
                static function (General $general) use ($firstname, $lastname, $phone, $email): void {
                    $general->firstname = $firstname;
                    $general->lastname = $lastname;
                    $general->phone = $phone;
                    $general->email = $email;
                },
            ),
            'personal_details' => PersonalDetails::newInstanceWithVersion(
                1,
                static function (PersonalDetails $personalDetails) use ($dateOfBirth): void {
                    $personalDetails->dateOfBirth = $dateOfBirth;
                },
            ),
            'label' => sprintf('%s %s', $firstname, $lastname),
            'search_date_of_birth' => $dateOfBirthHash,
            'search_email' => $emailHash,
            'search_phone' => $phoneHash,
            'date_of_last_exposure' => CarbonImmutable::now(),
            'category' => null,
        ]);
    }
}

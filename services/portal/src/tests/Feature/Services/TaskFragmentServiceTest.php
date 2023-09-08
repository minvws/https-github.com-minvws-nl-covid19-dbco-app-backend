<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Eloquent\EloquentTask;
use App\Models\Shared\VaccineInjection;
use App\Models\Task\AlternateContact;
use App\Models\Task\Circumstances;
use App\Models\Task\General;
use App\Models\Task\Immunity;
use App\Models\Task\Job;
use App\Models\Task\PersonalDetails;
use App\Models\Task\Symptoms;
use App\Models\Task\TaskAddress;
use App\Models\Task\Test;
use App\Models\Task\Vaccination;
use App\Services\TaskFragmentService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTestCase;

class TaskFragmentServiceTest extends FeatureTestCase
{
    private EloquentTask $task;
    private TaskFragmentService $taskFragmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->taskFragmentService = $this->app->get(TaskFragmentService::class);
    }

    protected function tearDown(): void
    {
        $this->assertDatabaseHas('task', ['uuid' => $this->task->uuid]);

        parent::tearDown();
    }

    public function testStoreGeneralFragmentMaxLength(): void
    {
        $general = new General();
        $general->reference = Str::random(7);
        $general->firstname = Str::random(250);
        $general->lastname = Str::random(500);
        $general->email = Str::random(250);
        $general->phone = Str::random(25);
        $general->label = Str::random(100);
        $general->context = Str::random(500);
        $general->otherRelationship = Str::random(500);
        $general->nature = Str::random(500);

        $this->taskFragmentService->storeFragment($this->task->uuid, 'general', $general);
    }

    public function testStoreCircumstancesFragmentMaxLength(): void
    {
        $circumstances = Circumstances::getSchema()->getCurrentVersion()->newInstance();
        $circumstances->ppeType = Str::random(250);
        $circumstances->ppeReplaceFrequency = Str::random(500);

        $this->taskFragmentService->storeFragment($this->task->uuid, 'circumstances', $circumstances);
    }

    public function testStoreSymptomsFragmentMaxLength(): void
    {
        $otherSymptoms = [];
        for ($i = 0; $i < 25; $i++) {
            $otherSymptoms[] = Str::random(100);
        }

        /** @var Symptoms $symptoms */
        $symptoms = Symptoms::newInstanceWithVersion(1);
        $symptoms->otherSymptoms = $otherSymptoms;

        $this->taskFragmentService->storeFragment($this->task->uuid, 'symptoms', $symptoms);
    }

    public function testStoreTestFragmentMaxLength(): void
    {
        $test = Test::newInstanceWithVersion(1);
        $test->previousInfectionHpzoneNumber = Str::random(500);

        $this->taskFragmentService->storeFragment($this->task->uuid, 'test', $test);
    }

    public function testStoreVaccinationFragmentMaxLength(): void
    {
        $vaccineInjection = VaccineInjection::newInstanceWithVersion(1);
        $vaccineInjection->otherVaccineType = Str::random(500);

        $vaccination = Vaccination::newInstanceWithVersion(1);
        $vaccination->vaccineInjections = [$vaccineInjection];

        $this->taskFragmentService->storeFragment($this->task->uuid, 'vaccination', $vaccination);
    }

    public function testStorePersonalDetailsFragmentMaxLength(): void
    {
        $taskAddress = TaskAddress::newInstanceWithVersion(1);
        $taskAddress->postalCode = Str::random(10);
        $taskAddress->houseNumber = Str::random(10);
        $taskAddress->houseNumberSuffix = Str::random(10);
        $taskAddress->street = Str::random(250);
        $taskAddress->town = Str::random(250);

        $personalDetails = new PersonalDetails();
        $personalDetails->address = $taskAddress;
        $personalDetails->bsnCensored = Str::random(9);
        $personalDetails->bsnLetters = Str::random(25);

        $this->taskFragmentService->storeFragment($this->task->uuid, 'personal_details', $personalDetails);
    }

    public function testStoreAlternateContactFragmentMaxLength(): void
    {
        $alternateContact = new AlternateContact();
        $alternateContact->firstname = Str::random(500);
        $alternateContact->lastname = Str::random(500);
        $alternateContact->explanation = Str::random(500);

        $this->taskFragmentService->storeFragment($this->task->uuid, 'alternate_contact', $alternateContact);
    }

    public function testStoreJobFragmentMaxLength(): void
    {
        $job = new Job();
        $job->healthCareFunction = Str::random(500);

        $this->taskFragmentService->storeFragment($this->task->uuid, 'job', $job);
    }

    public function testStoreImmunityFragmentMaxLength(): void
    {
        $immunity = Immunity::newInstanceWithVersion(1);
        $immunity->remarks = Str::random(500);

        $this->taskFragmentService->storeFragment($this->task->uuid, 'immunity', $immunity);
    }
}

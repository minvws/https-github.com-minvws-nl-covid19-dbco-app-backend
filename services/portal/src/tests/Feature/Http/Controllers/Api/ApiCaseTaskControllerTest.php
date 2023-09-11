<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentQuestionnaire;
use App\Models\Eloquent\EloquentUser;
use App\Models\Task\General;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use TRegx\PhpUnit\DataProviders\DataProvider as TRegxDataProvider;

use function config;

#[Group('case-task')]
final class ApiCaseTaskControllerTest extends FeatureTestCase
{
    public function testCreateCaseTaskWithoutCategoryShouldNotLinkDefaultCategory(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_test' => CarbonImmutable::now(),
            'date_of_symptom_onset' => CarbonImmutable::now(),
        ]);

        // check minimum fields required for storage
        $response = $this->be($user)->postJson('/api/cases/' . $case->uuid . '/tasks', [
            'task' => [
                'dateOfLastExposure' => null,
                'group' => 'positivesource',
                'label' => 'James',
            ],
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(null, $data['task']['category']);
    }

    public function testGetIncompleteTaskForAnyCategoryShouldShowIncompleteProgress(): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => $this->faker->randomElement(ContactCategory::all()),
            'date_of_last_exposure' => CarbonImmutable::now()->min('2 days'),
        ]);

        $data = $this->getContactTasks($user, $case->uuid);
        $this->assertEquals('incomplete', $data['tasks'][0]['progress']);
    }

    #[DataProvider('contactableTaskOptionsProviderCat1And2')]
    public function testGetContactableTaskShouldShowContactableProgressForCat1And2(
        ContactCategory $contactCategory,
        ?string $firstname,
        ?string $lastname,
        ?string $phone,
        ?string $questionnaireFirstname,
        ?string $questionnaireLastname,
        ?string $questionnairePhone,
    ): void {
        $options = CreateDummyTaskOptions::none();
        $options->dateOfLastExposure = CarbonImmutable::now()->min('2 days');
        $options->category = $contactCategory;
        $options->firstname = $firstname;
        $options->lastname = $lastname;
        $options->phone = $phone;
        $options->questionnaireFirstname = $questionnaireFirstname;
        $options->questionnaireLastname = $questionnaireLastname;
        $options->questionnairePhone = $questionnairePhone;

        CarbonImmutable::setTestNow('2020-01-01');
        $user = $this->createUser();
        $case = $this->createTestCaseWithOptionsForUser($user, $options);

        $data = $this->getContactTasks($user, $case->uuid);
        $this->assertEquals('contactable', $data['tasks'][0]['progress']);
    }

    public static function contactableTaskOptionsProviderCat1And2(): iterable
    {
        return TRegxDataProvider::cross(
            [
                [ContactCategory::cat1()],
                [ContactCategory::cat2a()],
                [ContactCategory::cat2b()],
            ],
            [
                [null, null, null, 'James', null, '06 22 22 22 22'],
                [null, null, null, null, 'Bond', '06 22 22 22 22'],
                ['James', null, '06 22 22 22 22', null, null, null],
                [null, 'Bond', '06 22 22 22 22', null, null, null],
                ['James', null, null, null, null, '06 22 22 22 22'],
                [null, null, '06 22 22 22 22', 'James', null, null],
                [null, 'Bond', null, null, null, '06 22 22 22 22'],
                [null, null, '06 22 22 22 22', null, 'Bond', null],
            ],
        );
    }

    #[DataProvider('contactableTaskOptionsProviderCat3')]
    public function testGetContactableTaskShouldShowContactableProgressForCat3(
        ContactCategory $contactCategory,
        ?string $email,
        ?string $questionnaireEmail,
    ): void {
        $options = CreateDummyTaskOptions::none();
        $options->dateOfLastExposure = CarbonImmutable::now()->min('2 days');
        $options->category = $contactCategory;
        $options->email = $email;
        $options->questionnaireEmail = $questionnaireEmail;

        CarbonImmutable::setTestNow('2020-01-01');
        $user = $this->createUser();
        $case = $this->createTestCaseWithOptionsForUser($user, $options);

        $data = $this->getContactTasks($user, $case->uuid);
        $this->assertEquals('contactable', $data['tasks'][0]['progress']);
    }

    public static function contactableTaskOptionsProviderCat3(): iterable
    {
        return TRegxDataProvider::cross(
            [
                [ContactCategory::cat3a()],
                [ContactCategory::cat3b()],
            ],
            [
                [null, 'james@bond.com'],
                ['james@bond.com', null],
            ],
        );
    }

    #[DataProvider('completeTaskOptionsProvider')]
    public function testGetCompletedTaskShouldShowCompleteProgress(
        ContactCategory $contactCategory,
        ?string $firstname,
        ?string $lastname,
        ?string $phone,
        ?string $email,
        ?string $questionnaireFirstname,
        ?string $questionnaireLastname,
        ?string $questionnairePhone,
        ?string $questionnaireEmail,
    ): void {
        $options = CreateDummyTaskOptions::none();
        $options->dateOfLastExposure = CarbonImmutable::now()->min('2 days');
        $options->category = $contactCategory;
        $options->firstname = $firstname;
        $options->lastname = $lastname;
        $options->phone = $phone;
        $options->email = $email;
        $options->questionnaireFirstname = $questionnaireFirstname;
        $options->questionnaireLastname = $questionnaireLastname;
        $options->questionnairePhone = $questionnairePhone;
        $options->questionnaireEmail = $questionnaireEmail;

        CarbonImmutable::setTestNow('2020-01-01');
        $user = $this->createUser();
        $case = $this->createTestCaseWithOptionsForUser($user, $options);

        $data = $this->getContactTasks($user, $case->uuid);
        $this->assertEquals('complete', $data['tasks'][0]['progress']);
    }

    public static function completeTaskOptionsProvider(): iterable
    {
        return TRegxDataProvider::cross(
            TRegxDataProvider::dictionary(ContactCategory::all()),
            [
                ['James', 'Bond', '06 22 22 22 22', 'james@bond.com', null, null, null, null],
                [null, null, null, null, 'James', 'Bond', '06 22 22 22 22', 'james@bond.com'],
                ['James', null, null, null, null, 'Bond', '06 22 22 22 22', 'james@bond.com'],
                [null, 'Bond', null, null, 'James', null, '06 22 22 22 22', 'james@bond.com'],
                [null, null, '06 22 22 22 22', null, 'James', 'Bond', null, 'james@bond.com'],
                [null, null, null, 'james@bond.com', 'James', 'Bond', '06 22 22 22 22', null],
            ],
        );
    }

    public function testGetNonAccessibleTaskShouldOnlyReturnSafeData(): void
    {
        $user = $this->createUser();

        $options = CreateDummyTaskOptions::none();
        $options->firstname = 'John';
        $options->lastname = 'Snow';
        $options->context = 'My Favourite';
        $case = $this->createTestCaseWithOptionsForUser($user, $options);

        $taskAvailablityInDays = 28;
        config()->set('misc.encryption.task_availability_in_days', $taskAvailablityInDays);
        CarbonImmutable::setTestNow(CarbonImmutable::now()->addDays($taskAvailablityInDays));

        $data = $this->getContactTasks($user, $case->uuid);

        $this->assertFalse($data['tasks'][0]['accessible']);
        $this->assertNotContains('label', $data['tasks'][0]);
        $this->assertNotContains('task_context', $data['tasks'][0]);
        $this->assertNull($data['tasks'][0]['progress']);
    }

    private function getContactTasks(EloquentUser $user, string $caseUuid): array
    {
        $response = $this->be($user)->getJson('/api/cases/' . $caseUuid . '/tasks/contact?includeProgress=true');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(1, $data['tasks']);

        return $data;
    }

    private function createTestCaseWithOptionsForUser(EloquentUser $user, CreateDummyTaskOptions $options): EloquentCase
    {
        /** @var EloquentQuestionnaire $questionnaire */
        $questionnaire = EloquentQuestionnaire::factory()->create();

        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'task_group' => $options->taskGroup ?? TaskGroup::contact(),
            'label' => 'dummy',
            'task_context' => $options->context,
            'category' => $options->category,
            'date_of_last_exposure' => $options->dateOfLastExposure,
            'communication' => '',
            'is_source' => false,
            'questionnaire_uuid' => $questionnaire->uuid,
            'general' => General::newInstanceWithVersion(1, static function (General $general) use ($options): void {
                $general->firstname = $options->firstname;
                $general->lastname = $options->lastname;
                $general->email = $options->email;
                $general->phone = $options->phone;
            }),
        ]);
        $question = $this->createQuestionForQuestionnaire($questionnaire, [
            'identifier' => 'contactdetails',
            'group_name' => 'contactdetails',
            'question_type' => 'contactdetails',
            'relevant_for_categories' => '1,2a,2b,3a,3b',
        ]);
        $this->createAnswerForTaskWithQuestion($task, $question, [
            'ctd_email' => $options->questionnaireEmail,
            'ctd_firstname' => $options->questionnaireFirstname,
            'ctd_lastname' => $options->questionnaireLastname,
            'ctd_phonenumber' => $options->questionnairePhone,
        ]);

        return $case;
    }
}

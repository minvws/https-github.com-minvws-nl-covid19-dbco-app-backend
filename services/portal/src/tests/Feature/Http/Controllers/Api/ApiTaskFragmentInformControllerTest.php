<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\EloquentQuestionnaire;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task\General;
use App\Models\Task\Inform;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\InformedBy;
use MinVWS\DBCO\Enum\Models\InformStatus;
use MinVWS\DBCO\Enum\Models\InformTarget;
use MinVWS\DBCO\Enum\Models\TaskAdvice;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_keys;
use function sprintf;

#[Group('case-fragment')]
#[Group('case-fragment-inform')]
class ApiTaskFragmentInformControllerTest extends FeatureTestCase
{
    public function testWhenInformStatusIsInformedThenInformedByStaffAtShouldBeSet(): void
    {
        CarbonImmutable::setTestNow('2020');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->firstname = 'foo';
                $general->lastname = 'bar';
                $general->phone = '0123';
                $general->email = 'foo@bar.com';
            }),
        ]);

        $response = $this->be($user)->putJson('/api/tasks/' . $task->uuid . '/fragments/inform', [
            'status' => InformStatus::informed(),
        ]);
        $response->assertStatus(200);

        // check if informed_by_staff_at has been set
        $dbTask = EloquentTask::find($task->uuid);
        $this->assertInstanceOf(DateTimeInterface::class, $dbTask['informed_by_staff_at']);
        $this->assertEquals(CarbonImmutable::now(), $dbTask['informed_by_staff_at']);

        // check if informed event (metric) was registered
        $this->assertDatabaseHas('event', [
            'type' => 'informed',
        ]);
    }

    public function testPostValuesAreStoredAndCanBeRetrievedAfterwards(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')]);

        $input = [
            'status' => InformStatus::informed()->value,
            'informedBy' => InformedBy::staff()->value,
            'shareIndexNameWithContact' => true,
            'informTarget' => InformTarget::representative(),
        ];

        $testFragmentData = function (array $output) use ($input): void {
            foreach (array_keys($input) as $fieldName) {
                $this->assertEquals(
                    $input[$fieldName],
                    $output[$fieldName],
                    sprintf('Failed asserting that field %s is in response.', $fieldName),
                );
            }
        };

        $uri = sprintf('/api/tasks/%s/fragments', $task->uuid);
        $response = $this->be($user)->putJson($uri, ['inform' => $input]);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['inform']);

        // check storage
        $response = $this->be($user)->get($uri);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['inform']);

        // check if task fields are saved
        $dbTask = EloquentTask::find($task->uuid);
        $this->assertSame(InformStatus::informed(), $dbTask['inform_status']);
        $this->assertSame(InformedBy::staff(), $dbTask['communication']);
    }

    public function testPostAdviceValuesAreStoredAndCanBeRetrievedAfterwardsInformUpToV1(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')], ['schema_version' => 2]);

        $input = [
            'advices' => [
                TaskAdvice::doTestAsap(),
                TaskAdvice::liveSeperatedExplained(),
            ],
            'testAdvice' => 'Test tomorrow',
        ];

        $testFragmentData = function (array $output) use ($input): void {
            foreach (array_keys($input) as $fieldName) {
                $this->assertEquals(
                    $input[$fieldName],
                    $output[$fieldName],
                    sprintf('Failed asserting that field %s is in response.', $fieldName),
                );
            }
        };

        $uri = sprintf('/api/tasks/%s/fragments', $task->uuid);
        $response = $this->be($user)->putJson($uri, ['inform' => $input]);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['inform']);

        // check storage
        $response = $this->be($user)->get($uri);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['inform']);

        // check if task fields are saved
        $dbTask = EloquentTask::find($task->uuid);
        $this->assertSame('Test tomorrow', $dbTask['inform']->testAdvice);
        $this->assertSame(TaskAdvice::doTestAsap(), $dbTask['inform']->advices[0]);
        $this->assertSame(TaskAdvice::liveSeperatedExplained(), $dbTask['inform']->advices[1]);
        $this->assertSame(1, $dbTask->inform->getSchemaVersion()->getVersion());
    }

    public function testPostAdviceValuesAreStoredAndCanBeRetrievedAfterwardsInformV2UpToV6(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, ['created_at' => new DateTimeImmutable('now')], ['schema_version' => 6]);

        $input = [
            'advices' => [
                TaskAdvice::complaintsSelftest(),
                TaskAdvice::complaintsTestGgd(),
            ],
            'vulnerableGroupsAdvice' => 'Test user input',
        ];

        $testFragmentData = function (array $output) use ($input): void {
            foreach (array_keys($input) as $fieldName) {
                $this->assertEquals(
                    $input[$fieldName],
                    $output[$fieldName],
                    sprintf('Failed asserting that field %s is in response.', $fieldName),
                );
            }
        };

        $uri = sprintf('/api/tasks/%s/fragments', $task->uuid);
        $response = $this->be($user)->putJson($uri, ['inform' => $input]);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['inform']);

        // check storage
        $response = $this->be($user)->get($uri);
        $response->assertStatus(200);
        $testFragmentData($response->json()['data']['inform']);

        // check if task fields are saved
        $dbTask = EloquentTask::find($task->uuid);
        $this->assertSame(TaskAdvice::complaintsSelftest(), $dbTask['inform']->advices[0]);
        $this->assertSame(TaskAdvice::complaintsTestGgd(), $dbTask['inform']->advices[1]);
        $this->assertSame('Test user input', $dbTask['inform']->vulnerableGroupsAdvice);
        $this->assertSame(2, $dbTask->inform->getSchemaVersion()->getVersion());
    }

    public function testPostLoadValuesIntoFragment(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser(
            $user,
            [
                'created_at' => new DateTimeImmutable('now'),
                'communication' => InformedBy::staff(),
                'inform_status' => InformStatus::informed(),
            ],
        );

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments', $task->uuid));
        $response->assertStatus(200);

        $data = $response->json('data')['inform'];
        $this->assertEquals(InformedBy::staff()->value, $data['informedBy']);
        $this->assertEquals(InformStatus::informed()->value, $data['status']);
    }

    public static function shareIndexNameWithContactDataProvider(): array
    {
        return [
            'yes' => [YesNoUnknown::yes()->value, true],
            'no' => [YesNoUnknown::no()->value, false],
            'null' => [null, null],
        ];
    }

    #[DataProvider('shareIndexNameWithContactDataProvider')]
    public function testGivenQuestionnaireAnswerFromAppShouldSetValueInPortalWhenNotAlreadySet(?string $givenAnswer, ?bool $expectedOutput): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $questionnaire = EloquentQuestionnaire::factory()->create();
        $task = $this->createTaskForCase($case, [
            'questionnaire_uuid' => $questionnaire->uuid,
            'created_at' => CarbonImmutable::now(),
        ]);
        $question = $this->createQuestionForQuestionnaire($questionnaire, [
            'identifier' => 'mention',
            'group_name' => 'contactdetails',
            'question_type' => 'multiplechoice',
        ]);
        $this->createAnswerForTaskWithQuestion($task, $question, [
            'spv_value' => $givenAnswer,
        ]);

        $response = $this->be($user)->get('/api/tasks/' . $task->uuid . '/fragments/inform');
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($expectedOutput, $data['data']['shareIndexNameWithContact']);
    }

    public static function shareNotOverrideIndexNameWithContactDataProvider(): array
    {
        return [
            'yes should stay yes when trying to overwrite with no from app' => [true, YesNoUnknown::no()->value, true],
            'no should stay no when trying to overwrite with yes from app' => [false, YesNoUnknown::yes()->value, false],
            'no should stay no when trying to overwrite with no from app' => [false, YesNoUnknown::no()->value, false],
            'yes should stay yes when trying to overwrite with yes from app' => [true, YesNoUnknown::yes()->value, true],
            'yes should stay yes when trying to overwrite with null from app' => [true, null, true],
            'no should stay no when trying to overwrite with null from app' => [false, null, false],
            'null should stay null when trying to overwrite with null from app' => [null, null, null],
            'null should be overwritten with no when trying to overwrite with no from app' => [null, YesNoUnknown::no()->value, false],
            'null should be overwritten with yes when trying to overwrite with yes from app' => [null, YesNoUnknown::yes()->value, true],
        ];
    }

    #[DataProvider('shareNotOverrideIndexNameWithContactDataProvider')]
    public function testGivenQuestionnaireAnswerFromAppShouldNotOverrideValueSetInPortal(?bool $currentValue, ?string $givenAnswer, ?bool $expectedOutput): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $questionnaire = EloquentQuestionnaire::factory()->create();

        $task = $this->createTaskForCase($case);
        $task->questionnaire_uuid = $questionnaire->uuid;
        $task->created_at = CarbonImmutable::now();
        $task->inform = Inform::newInstanceWithVersion(
            Inform::getSchema()->getCurrentVersion()->getVersion(),
            static function (Inform $inform) use ($currentValue): void {
                $inform->shareIndexNameWithContact = $currentValue;
                $inform->status = InformStatus::informed();
            },
        );
        $task->save();

        $question = $this->createQuestionForQuestionnaire($questionnaire, [
            'identifier' => 'mention',
            'group_name' => 'contactdetails',
            'question_type' => 'multiplechoice',
        ]);
        $this->createAnswerForTaskWithQuestion($task, $question, [
            'spv_value' => $givenAnswer,
        ]);

        $response = $this->be($user)->get('/api/tasks/' . $task->uuid . '/fragments/inform');
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($expectedOutput, $data['data']['shareIndexNameWithContact']);
    }
}

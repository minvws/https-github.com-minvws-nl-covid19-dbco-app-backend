<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Task\General;
use App\Services\BcoNumber\BcoNumberGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function json_decode;

#[Group('task-fragment-general')]
#[Group('task')]
final class ApiTaskFragmentGeneralControllerTest extends FeatureTestCase
{
    #[Group('task-fragment-general-prohibited')]
    public function testCreateWithEmptyCaseShouldNotAllowDateOfLastExposure(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat1(),
            'task_group' => TaskGroup::contact(),
        ]);

        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'dateOfLastExposure' => CarbonImmutable::now()->subDays(20)->format("Y-m-d"),
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('validationResult', $data);
        $this->assertArrayHasKey('Prohibited', $data['validationResult']['warning']['failed']['dateOfLastExposure']);
        $this->assertStringContainsString(
            'Veld "Laatste contact datum" kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case',
            $data['validationResult']['warning']['errors']['dateOfLastExposure'][0],
        );
    }

    public function testCreateWithLastExposureDateInPastShouldFail(): void
    {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => new CarbonImmutable('3 days ago'),
            'date_of_test' => CarbonImmutable::yesterday(),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat1(),
            'task_group' => TaskGroup::contact(),
        ]);

        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'category' => "3a",
            'dateOfLastExposure' => CarbonImmutable::now()->subDays(20)->format("Y-m-d"),
            'relationship' => 'parent',
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('validationResult', $data);
        $this->assertArrayHasKey('AfterOrEqual', $data['validationResult']['warning']['failed']['dateOfLastExposure']);
        $this->assertStringContainsString(
            'Veld "Laatste contact datum" moet later zijn dan',
            $data['validationResult']['warning']['errors']['dateOfLastExposure'][0],
        );
    }

    public function testUpdateWithLastExposureDateInPastShouldSucceedWithWarning(): void
    {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => new CarbonImmutable('3 days ago'),
            'date_of_test' => CarbonImmutable::yesterday(),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat1(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->category = ContactCategory::cat1();
            }),
            'task_group' => TaskGroup::contact(),
        ]);

        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'category' => "3a",
            'dateOfLastExposure' => CarbonImmutable::now()->subDays(20)->format("Y-m-d"),
            'relationship' => 'parent',
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('validationResult', $data);
        $this->assertArrayHasKey('AfterOrEqual', $data['validationResult']['warning']['failed']['dateOfLastExposure']);
        $this->assertStringContainsString(
            'Veld "Laatste contact datum" moet later zijn dan',
            $data['validationResult']['warning']['errors']['dateOfLastExposure'][0],
        );
    }

    public function testCreateWithLastExposureDateInFutureShouldFail(): void
    {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => new CarbonImmutable('3 days ago'),
            'date_of_test' => CarbonImmutable::yesterday(),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $task = $this->createTaskForCase($case, [
            'category' => ContactCategory::cat1(),
            'task_group' => TaskGroup::contact(),
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'category' => "3a",
            'dateOfLastExposure' => CarbonImmutable::tomorrow()->format("Y-m-d"),
            'relationship' => 'parent',
        ]);
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('validationResult', $data);
        $this->assertArrayHasKey('BeforeOrEqual', $data['validationResult']['warning']['failed']['dateOfLastExposure']);
        $this->assertStringContainsString(
            'Veld "Laatste contact datum" moet gelijk of eerder zijn dan',
            $data['validationResult']['warning']['errors']['dateOfLastExposure'][0],
        );
    }

    public function testCreateWithLastExposureDateAfterContagiousPeriodShouldFail(): void
    {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => new CarbonImmutable('15 days ago'),
            'date_of_test' => new CarbonImmutable('10 days ago'),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $task = $this->createTaskForCase($case, [
            'category' => ContactCategory::cat1(),
            'task_group' => TaskGroup::contact(),
            'created_at' => CarbonImmutable::now(),
        ]);

        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'category' => "3a",
            'dateOfLastExposure' => CarbonImmutable::yesterday()->format("Y-m-d"),
            'relationship' => 'parent',
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('validationResult', $data);
        $this->assertArrayHasKey('BeforeOrEqual', $data['validationResult']['warning']['failed']['dateOfLastExposure']);
        $this->assertStringContainsString(
            'Veld "Laatste contact datum" moet gelijk of eerder zijn dan',
            $data['validationResult']['warning']['errors']['dateOfLastExposure'][0],
        );
    }

    public function testCreateForPositiveSourceTaskWithLastExposureDateBeforeContagiousPeriodShouldPass(): void
    {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => new CarbonImmutable('15 days ago'),
            'date_of_test' => new CarbonImmutable('10 days ago'),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat1(),
            'task_group' => TaskGroup::positiveSource(),
        ]);

        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'category' => "3a",
            'dateOfLastExposure' => (new CarbonImmutable('19 days ago'))->format("Y-m-d"),
            'relationship' => 'parent',
            'remarks' => 'Test',
        ]);
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertFalse(isset($data['validationResult']));
        $this->assertEquals('Test', $response->json('data.remarks'));
    }

    public function testWithPersonalDataChangeShouldCreateEditedMetric(): void
    {
        $user = $this->createUser();

        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => new CarbonImmutable('15 days ago'),
            'date_of_test' => new CarbonImmutable('10 days ago'),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat1(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->category = ContactCategory::cat1();
            }),
            'date_of_last_exposure' => new CarbonImmutable('14 days ago'),
        ]);
        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'lastname' => "Doe",
            'firstname' => "John",
            'dateOfLastExposure' => (new CarbonImmutable('12 days ago'))->format('Y-m-d'),
        ]);
        $response->assertStatus(200);

        $eventExportDataJson = DB::table('event')->where('type', 'edited')->value('export_data');
        $eventExportData = json_decode($eventExportDataJson, true);
        $this->assertStringContainsString('lastname', $eventExportData['fields']);
        $this->assertStringContainsString('firstname', $eventExportData['fields']);
        $this->assertStringContainsString('date_of_last_exposure', $eventExportData['fields']);
    }

    public function testWithPersonalDataChangeShouldCreateInventoriedMetric(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => new CarbonImmutable('15 days ago'),
            'date_of_test' => new CarbonImmutable('10 days ago'),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat1(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->category = ContactCategory::cat1();
            }),
        ]);
        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'lastname' => "Doe",
            'firstname' => "John",
            'dateOfLastExposure' => (new CarbonImmutable('12 days ago'))->format('Y-m-d'),
        ]);
        $response->assertStatus(200);

        $eventExportDataJson = DB::table('event')->where('type', 'inventoried')->value('export_data');
        $eventExportData = json_decode($eventExportDataJson, true);
        $this->assertStringContainsString('lastname', $eventExportData['fields']);
        $this->assertStringContainsString('firstname', $eventExportData['fields']);
        $this->assertStringContainsString('date_of_last_exposure', $eventExportData['fields']);
    }

    /**
     * The general reference field contains the case number and should be copied to the field task.dossieur_number
     * so that this is still available after pseudonymization.
     *
     * Currently this should only be supported for all task groups not equal to "contact".
     */
    public function testWithGeneralReferenceShouldCopyToDossierNumberUponStore(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'task_group' => TaskGroup::positiveSource(),
            'category' => ContactCategory::cat1(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->category = ContactCategory::cat1();
            }),
        ]);
        $this->be($user);

        $this->assertDatabaseMissing('task', ['uuid' => $task->uuid, 'dossier_number' => '1234567']);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'reference' => "1234567",
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('task', ['uuid' => $task->uuid, 'dossier_number' => '1234567']);
    }

    /**
     * The general reference field contains the case number and should be copied to the field task.dossieur_number
     * so that this is still available after pseudonymization.
     *
     * Currently this should only be supported for all task groups not equal to "contact".
     */
    public function testWithGeneralReferenceShouldNotCopyToDossierNumberUponStoreWhenGeneralContact(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'task_group' => TaskGroup::contact(),
            'category' => ContactCategory::cat1(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->category = ContactCategory::cat1();
            }),
        ]);
        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'reference' => "1234567",
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('task', ['uuid' => $task->uuid, 'dossier_number' => '1234567']);
    }

    /**
     * In the event that the dossier_number is different in the task table, that value should always have
     * precedence on any value in the fragment general.reference.
     */
    public function testDossierNumberOnTaskHasPriorityOnGeneralFragmentValue(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'task_group' => TaskGroup::positiveSource(),
            'category' => ContactCategory::cat1(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->category = ContactCategory::cat1();
                $general->reference = '7654321';
            }),
            'dossier_number' => '7654321',
        ]);
        $this->be($user);

        $response = $this->getJson('/api/tasks/' . $task->uuid . '/fragments/general');
        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals('7654321', $data['data']['reference']);
    }

    public function testRetrieveFieldFromTaskWhichHasProxyFieldWithDefaultValue(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'task_group' => TaskGroup::positiveSource(),
            'category' => ContactCategory::cat1(),
            'is_source' => true,
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->category = ContactCategory::cat1();
                $general->reference = '7654321';
                $general->isSource = true;
            }),
        ]);
        $this->be($user);

        $response = $this->getJson('/api/cases/' . $case->uuid . '/tasks/positivesource');
        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals(true, $data['tasks'][0]['isSource']);
    }

    public function testTaskGeneralReferenceShouldAcceptBcoPortalNumber(): void
    {
        $bcoNumberGenerator = new BcoNumberGenerator('123456789', 'ABCDEFGHIJKLMNOPQRSTUVW');
        $reference = $bcoNumberGenerator->buildCode();

        $user = $this->createUser();
        $task = $this->createTaskForUser($user, [
            'task_group' => TaskGroup::contact(),
        ]);
        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'reference' => $reference,
        ]);

        $response->assertStatus(200);
        $this->assertSame($reference, $response->json('data')['reference']);
    }

    public function testTaskGeneralReferenceShouldAcceptHpZoneNumber(): void
    {
        $reference = (string) $this->faker->numberBetween(1_000_000, 99_999_999);

        $user = $this->createUser();
        $task = $this->createTaskForUser($user, [
            'task_group' => TaskGroup::contact(),
        ]);
        $this->be($user);

        $response = $this->putJson('/api/tasks/' . $task->uuid . '/fragments/general', [
            'reference' => $reference,
        ]);

        $response->assertStatus(200);
        $this->assertSame($reference, $response->json('data')['reference']);
    }
}

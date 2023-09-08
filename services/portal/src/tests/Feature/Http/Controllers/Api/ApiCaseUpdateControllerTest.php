<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\General;
use App\Models\CovidCase\Symptoms as CaseSymptoms;
use App\Models\CovidCase\Test as CaseTest;
use App\Models\Eloquent\CaseUpdate;
use App\Models\Eloquent\CaseUpdateContact;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\Task\General as ContactGeneral;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function config;
use function date;
use function sprintf;
use function strtotime;

#[Group('case-update')]
class ApiCaseUpdateControllerTest extends FeatureTestCase
{
    private EloquentUser $user;
    private EloquentCase $case;
    private CaseUpdate $caseUpdate;
    private CaseUpdateContact $caseUpdateContact;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = $this->createOrganisation();
        $this->user = $this->createUserForOrganisation($organisation);
        $this->case = $this->createCaseForOrganisation($organisation, ['assigned_user_uuid' => $this->user->uuid]);
        $this->caseUpdate = $this->createCaseUpdateForCase($this->case);

        $caseUpdateFragment = $this->caseUpdate->fragments()->make();
        $caseUpdateFragment->received_at = $this->caseUpdate->received_at;
        $caseUpdateFragment->name = 'general';
        $caseUpdateFragment->data = (object) [
            'askedAboutCoronaMelder' => false,
        ];
        $caseUpdateFragment->version = General::getSchema()->getCurrentVersion()->getVersion();
        $caseUpdateFragment->save();

        $caseUpdateFragment = $this->caseUpdate->fragments()->make();
        $caseUpdateFragment->received_at = $this->caseUpdate->received_at;
        $caseUpdateFragment->name = 'test';
        $caseUpdateFragment->data = (object) [
            'previousInfectionProven' => 'yes',
            'dateOfTest' => date('Y-m-d', strtotime('yesterday')),
        ];
        $caseUpdateFragment->version = CaseTest::getSchema()->getCurrentVersion()->getVersion();
        $caseUpdateFragment->save();

        $this->caseUpdateContact = $this->caseUpdate->contacts()->make();
        $this->caseUpdateContact->received_at = $this->caseUpdate->received_at;
        $this->caseUpdateContact->contactGroup = TaskGroup::positiveSource();
        $this->caseUpdateContact->label = 'BCO Broncontact';
        $this->caseUpdateContact->save();

        $caseUpdateContactFragment = $this->caseUpdateContact->fragments()->make();
        $caseUpdateContactFragment->received_at = $this->caseUpdate->received_at;
        $caseUpdateContactFragment->name = 'general';
        $caseUpdateContactFragment->data = (object) [
            'reference' => '1234567',
        ];
        $caseUpdateContactFragment->version = ContactGeneral::getSchema()->getCurrentVersion()->getVersion();
        $caseUpdateContactFragment->save();
    }

    private function addInvalidSymptomsFragment(): void
    {
        $caseUpdateFragment = $this->caseUpdate->fragments()->make();
        $caseUpdateFragment->received_at = $this->caseUpdate->received_at;
        $caseUpdateFragment->name = 'symptoms';
        $caseUpdateFragment->data = (object) [
            'hasSymptoms' => 'invalid_value',
        ];
        $caseUpdateFragment->version = CaseSymptoms::getSchema()->getCurrentVersion()->getVersion();
        $caseUpdateFragment->save();
    }

    public function testList(): void
    {
        config()->set('featureflag.intake_match_case_enabled', true);

        $response = $this->be($this->user)->getJson('/api/cases/' . $this->case->uuid . '/updates');
        $this->assertStatus($response, 200);
    }

    public function testListIfDisabled(): void
    {
        config()->set('featureflag.intake_match_case_enabled', false);

        $response = $this->be($this->user)->getJson(sprintf('/api/cases/%s/updates', $this->case->uuid));
        $this->assertStatus($response, 403);
    }

    public function testGet(): void
    {
        config()->set('featureflag.intake_match_case_enabled', true);

        $response = $this->be($this->user)->getJson('/api/cases/' . $this->case->uuid . '/updates/' . $this->caseUpdate->uuid);
        $this->assertStatus($response, 200);
    }

    public function testGetIfDisabled(): void
    {
        config()->set('featureflag.intake_match_case_enabled', false);

        $response = $this->be($this->user)->getJson('/api/cases/' . $this->case->uuid . '/updates/' . $this->caseUpdate->uuid);
        $this->assertStatus($response, 403);
    }

    #[Group('case-update-apply')]
    public function testApply(): void
    {
        config()->set('featureflag.intake_match_case_enabled', true);

        $this->assertCount(0, $this->case->tasks);

        $data = [
            'fieldIds' => [
                'contacts.' . $this->caseUpdateContact->uuid . '.general.reference',
            ],
        ];
        $response = $this->be($this->user)->postJson(
            '/api/cases/' . $this->case->uuid . '/updates/' . $this->caseUpdate->uuid . '/apply',
            $data,
        );
        $this->assertStatus($response, 204);
        $this->assertEmpty($response->getContent());

        $this->case->refresh();

        $this->assertSame(EloquentCase::getSchema()->getCurrentVersion()->getVersion(), $this->case->getSchemaVersion()->getVersion());
        $this->assertSame(General::getSchema()->getCurrentVersion()->getVersion(), $this->case->general->getSchemaVersion()->getVersion());

        $this->assertCount(1, $this->case->tasks);
        $this->assertEquals(TaskGroup::positiveSource(), $this->case->tasks[0]->task_group);
        $this->assertEquals('BCO Broncontact', $this->case->tasks[0]->label);
        $this->assertEquals('1234567', $this->case->tasks[0]->general->reference);
    }

    public function testValidationOnDiff(): void
    {
        config()->set('featureflag.intake_match_case_enabled', true);
        $this->addInvalidSymptomsFragment();

        $response = $this->be($this->user)->getJson('/api/cases/' . $this->case->uuid . '/updates/' . $this->caseUpdate->uuid);
        $this->assertStatus($response, 422);
        $response->assertJson([
            'validationResult' => [
                'symptoms' => [
                    'fatal' => [
                        'errors' => [
                            'hasSymptoms' => [],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testValidationOnApply(): void
    {
        config()->set('featureflag.intake_match_case_enabled', true);
        $this->addInvalidSymptomsFragment();

        $data = ['fieldIds' => ['symptoms.hasSymptoms']];
        $response = $this->be($this->user)->postJson(
            '/api/cases/' . $this->case->uuid . '/updates/' . $this->caseUpdate->uuid . '/apply',
            $data,
        );

        $this->assertStatus($response, 422);
        $response->assertJson([
            'validationResult' => [
                'symptoms' => [
                    'fatal' => [
                        'errors' => [
                            'hasSymptoms' => [],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testValidationOnApplyIgnoresInvalidFieldIfNotAppliedV1(): void
    {
        config()->set('featureflag.intake_match_case_enabled', true);
        $this->addInvalidSymptomsFragment();

        $data = ['fieldIds' => ['general.askedAboutCoronaMelder']];
        $response = $this->be($this->user)->postJson(
            '/api/cases/' . $this->case->uuid . '/updates/' . $this->caseUpdate->uuid . '/apply',
            $data,
        );
        $this->assertStatus($response, 204);
    }

    public function testValidationOnApplyIgnoresInvalidFieldIfNotAppliedV2(): void
    {
        config()->set('featureflag.intake_match_case_enabled', true);
        $this->addInvalidSymptomsFragment();

        $response = $this->be($this->user)->postJson('/api/cases/' . $this->case->uuid . '/updates/' . $this->caseUpdate->uuid . '/apply');
        $this->assertStatus($response, 204);
    }
}

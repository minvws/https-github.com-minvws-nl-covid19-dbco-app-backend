<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Jobs\ExportCaseToOsiris;
use App\Models\CovidCase\Index;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\OrganisationType;
use App\Models\StatusIndexContactTracing;
use Generator;
use Illuminate\Support\Facades\Bus;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

#[Group('contact-status')]
#[Group('case')]
final class ApiCaseUpdateContactStatusTest extends FeatureTestCase
{
    public function testWithValidPayloadShouldUpdateStatus(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $payload = [
            'status_index_contact_tracing' => ContactTracingStatus::notApproached()->value,
            'status_explanation' => 'Lorem Ipsum',
        ];
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/contact-status', $payload);
        $response->assertStatus(200);

        $data = $response->json('case');

        $this->assertEquals($payload['status_index_contact_tracing'], $data['statusIndexContactTracing']);
        $this->assertEquals($payload['status_explanation'], $data['statusExplanation']);
    }

    public function testWithValidMinimalPayloadShouldUpdateStatus(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $payload = [
            'status_index_contact_tracing' => 'not_approached',
        ];
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/contact-status', $payload);
        $response->assertStatus(200);

        $data = $response->json('case');

        $this->assertEquals($payload['status_index_contact_tracing'], $data['statusIndexContactTracing']);
        $this->assertEquals('', $data['statusExplanation']);
    }

    public function testEmptyOpenFieldPayloadShouldUpdateStatus(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'status_index_contact_tracing' => 'not_approached',
            'status_explanation' => 'test',
        ]);

        $payload = [
            'status_index_contact_tracing' => 'not_approached',
            'status_explanation' => '',
        ];
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/contact-status', $payload);

        $data = $response->json('case');

        $this->assertEquals($payload['status_index_contact_tracing'], $data['statusIndexContactTracing']);
        $this->assertEquals('', $data['statusExplanation']);
    }

    public function testWithInvalidStatusShouldFail(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $payload = ['status_index_contact_tracing' => 'invalid'];

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/contact-status', $payload);
        $response->assertStatus(422);
        $data = $response->json();

        $this->assertArrayHasKey('errors', $data);
        $this->assertEquals(['Veld "Status index contact tracing" is ongeldig.'], $data['errors']['status_index_contact_tracing']);
    }

    #[DataProvider('explanationRequiredProvider')]
    public function testExplanationRequired(StatusIndexContactTracing $statusIndexContactTracing): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $payload = ['status_index_contact_tracing' => $statusIndexContactTracing->value];

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/contact-status', $payload);
        $response->assertStatus(422);
        $data = $response->json();

        $this->assertArrayHasKey('errors', $data);
        $this->assertEquals(['Veld "Status explanation" is verplicht.'], $data['errors']['status_explanation']);
    }

    public static function explanationRequiredProvider(): Generator
    {
        yield 'Loose end' => [StatusIndexContactTracing::LOOSE_END()];
        yield 'Not reached' => [StatusIndexContactTracing::TWO_TIMES_NOT_REACHED()];
        yield 'Callback request' => [StatusIndexContactTracing::CALLBACK_REQUEST()];
    }

    /**
     * This use case is currently not possible because the auth middleware prevents outsourced user to access
     * the routing. But one off the acceptance criteria in DBCO-2102 states:
     *
     * "this state / checkbox is not available to landelijke partners."
     *
     * The functionality is therefore already supporting the eventuality that outsourced users will access this
     * endpoint, but without being able to confirm a complete case.
     */
    public function testWithCompleteStatusCheckByOutsourceUserShouldBeProhibited(): void
    {
        $user = $this->createUser([], null, ['type' => OrganisationType::outsourceOrganisation()->value]);
        $case = $this->createCaseForUser($user);

        $payload = [
            'status_index_contact_tracing' => StatusIndexContactTracing::COMPLETED(),
            'complete_status_checked' => true,
        ];
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/contact-status', $payload);

        $this->assertContains($response->status(), [403, 422]);

        if ($response->status() === 403) {
            return;
        }

        $data = $response->json();

        $this->assertArrayHasKey('errors', $data);
        $this->assertEquals(
            ['Status index contact tracing is ongeldig.'],
            $data['errors']['status_index_contact_tracing'],
        );
    }

    #[Group('osiris')]
    public function testWithIndexContactTracingCompletePayloadShouldTriggerOsirisJob(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');

        Bus::fake([ExportCaseToOsiris::class]);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                    $index->dateOfBirth = $this->faker->dateTime();
                    $index->gender = $this->faker->randomElement(Gender::all());
            }),
        ]);

        $payload = [
            'status_index_contact_tracing' => 'completed',
            'status_explanation' => 'Lorem Ipsum',
        ];
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/contact-status', $payload);
        $response->assertStatus(200);

        Bus::assertDispatched(
            static fn (ExportCaseToOsiris $sendToOsiris) => $sendToOsiris->caseExportType === CaseExportType::DEFINITIVE_ANSWERS,
        );
    }

    #[DataProvider('typeOsirisNotificationExamples')]
    #[Group('osiris')]
    public function testWithAnyPayloadAndForceOsirisNotificationShouldTriggerOsirisJob(
        string $typeNotification,
        CaseExportType $expectedExportType,
    ): void {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');

        Bus::fake([ExportCaseToOsiris::class]);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
                $index->gender = $this->faker->randomElement(Gender::all());
            }),
        ]);

        $payload = [
            'status_index_contact_tracing' => 'unknown',
            'status_explanation' => 'Lorem Ipsum',
        ];
        $response = $this->be($user)->putJson(
            '/api/cases/' . $case->uuid . '/contact-status?force_osiris_notification=' . $typeNotification,
            $payload,
        );
        $response->assertStatus(200);

        Bus::assertDispatched(
            static fn (ExportCaseToOsiris $sendToOsiris) => $sendToOsiris->caseExportType === $expectedExportType,
        );
    }

    public static function typeOsirisNotificationExamples(): array
    {
        return [
            ['finished', CaseExportType::DEFINITIVE_ANSWERS],
            ['pre-notification', CaseExportType::INITIAL_ANSWERS],
        ];
    }

    #[DataProvider('caseContactStatusProvider')]
    public function testReopeningClosedCase(array $bcoIndexStatus, array $newState, string $newBcoStatus, string $newIndexStatus): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, $bcoIndexStatus);
        $this->be($user);

        $response = $this->putJson('/api/cases/' . $case->uuid . '/contact-status', $newState);
        $response->assertStatus(200);

        $data = $response->json('case');

        $this->assertEquals($newBcoStatus, $data['bcoStatus']);
        $this->assertEquals($newIndexStatus, $data['indexStatus']);
    }

    public static function caseContactStatusProvider(): array
    {
        return [
            'Completed/Initial; closed_outside, not_approached' => [
                [
                    'bco_status' => BCOStatus::completed(),
                    'index_status' => IndexStatus::initial(),
                    'status_index_contact_tracing' => ContactTracingStatus::closedOutsideGgd(),
                ],
                ['status_index_contact_tracing' => ContactTracingStatus::notApproached()],
                BCOStatus::open()->value,
                IndexStatus::initial()->value,
            ],
            'Completed/Paired; closed_outside, not_approached' => [
                [
                    'bco_status' => BCOStatus::completed(),
                    'index_status' => IndexStatus::paired(),
                    'status_index_contact_tracing' => ContactTracingStatus::closedOutsideGgd(),
                ],
                ['status_index_contact_tracing' => ContactTracingStatus::notApproached()],
                BCOStatus::open()->value,
                IndexStatus::paired()->value,
            ],
        ];
    }

    #[DataProvider('userRolesProvider')]
    public function testGetContactStatusAccessibility(string $role, int $expectedStatus): void
    {
        $user = $this->createUser([], $role);
        $case = $this->createCaseForUser($user, [
            'bco_status' => BCOStatus::completed(),
            'index_status' => IndexStatus::initial(),
            'status_index_contact_tracing' => ContactTracingStatus::closedOutsideGgd(),
            'status_explanation' => 'Lorem Ipsum',
        ]);
        $this->be($user);

        $response = $this->getJson('/api/cases/' . $case->uuid . '/contact-status');
        $response->assertStatus($expectedStatus);

        if ($expectedStatus !== 200) {
            return;
        }

        $data = $response->json();

        $this->assertEquals([
            'bcoStatus' => BCOStatus::completed()->value,
            'indexStatus' => IndexStatus::initial()->value,
            'statusIndexContactTracing' => ContactTracingStatus::closedOutsideGgd()->value,
            'statusExplanation' => 'Lorem Ipsum',
        ], $data);
    }

    public static function userRolesProvider(): array
    {
        return [
            ['user', 200],
            ['planner', 200],
            ['compliance', 403],
        ];
    }
}

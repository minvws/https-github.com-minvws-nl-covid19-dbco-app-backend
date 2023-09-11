<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Console\Commands\PurgeSoftDeletedModels;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tests\Feature\FeatureTestCase;

use function route;
use function sprintf;

#[Group('compliance')]
#[Group('access-request')]
class ApiAccessRequestControllerTest extends FeatureTestCase
{
    private EloquentUser $user;
    private EloquentCase $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser(
            [
                'uuid' => '00000000-0000-0000-0001-000000000004',
                'external_id' => '345634243423424424',
                'name' => 'Compliance Officer',
            ],
            'compliance',
        );
        $this->case = $this->createCaseForUser($this->user, [
            'created_at' => CarbonImmutable::now(),
        ]);
        $this->createTaskForCase($this->case, ['created_at' => CarbonImmutable::now()]);
        $this->createTaskForCase($this->case, ['created_at' => CarbonImmutable::now()->subYear()]);
    }

    public function testFragmentsCase(): void
    {
        $response = $this->actingAs($this->user)->get(route('api-access-requests-fragments-case', $this->case->uuid));

        $response->assertJsonStructure([
            'data' => [
                'general',
                'index',
                'test',
            ],
        ]);
    }

    public function testFragmentsTask(): void
    {
        $task = $this->createTaskForCase($this->case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('api-access-requests-fragments-task', $task->uuid));

        $response->assertJsonStructure([
            'data' => [
                'index' => [
                    'general',
                ],
                'task' => [
                    'general',
                    'personalDetails',
                ],
            ],
        ]);
    }

    public function testDownloadIndexPdf(): void
    {
        $this->mockGenerateIndexPdf();

        $response = $this->actingAs($this->user)->get(route('api-access-requests-download-case', $this->case->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);
    }

    public function testDownloadIndexHtml(): void
    {
        $response = $this->actingAs($this->user)->get(route('api-access-requests-download-case-html', $this->case->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);
    }

    public function testDownloadIndexPdfAccessDenied(): void
    {
        // create new user, who should not have access
        $user = $this->createUserWithoutOrganisation();
        $user->organisations()->attach($this->user->organisations->first());

        $response = $this->actingAs($user)->get(route('api-access-requests-download-case', $this->case->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_FORBIDDEN);
    }

    public function testDownloadIndexPdfNonExistingCase(): void
    {
        $response = $this->actingAs($this->user)->get(route('api-access-requests-download-case', 'non-existing-uuid'));
        $response->assertStatus(SymfonyResponse::HTTP_NOT_FOUND);
    }

    public function testDownloadIndexPdfWithCookie(): void
    {
        $this->mockGenerateIndexPdf();

        $response = $this->actingAs($this->user)->get(
            route('api-access-requests-download-case', ['softDeletedCase' => $this->case->uuid, 'downloadCompleteToken' => 'abcdef123456']),
        );
        $response->assertStatus(SymfonyResponse::HTTP_OK);
        $response->assertCookie('downloadCompleteToken', 'abcdef123456', false);
    }

    public function testDownloadTaskPdf(): void
    {
        $this->mockGenerateTaskPdf();

        $task = $this->createTaskForCase($this->case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('api-access-requests-download-task', $task->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);
    }

    public function testDownloadTaskPdfAccessDenied(): void
    {
        $user = $this->createUser();
        $task = $this->createTaskForCase($this->case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->get(route('api-access-requests-download-task', $task->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_FORBIDDEN);
    }

    public function testDownloadTaskPdfNonExistingCase(): void
    {
        $response = $this->actingAs($this->user)->get(route('api-access-requests-download-task', 'non-existing-uuid'));
        $response->assertStatus(SymfonyResponse::HTTP_NOT_FOUND);
    }

    public function testDownloadTaskPdfWithCookie(): void
    {
        $this->mockGenerateTaskPdf();

        $task = $this->createTaskForCase($this->case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->actingAs($this->user)->get(
            route('api-access-requests-download-task', ['task' => $task->uuid, 'downloadCompleteToken' => 'abcdef123456']),
        );
        $response->assertStatus(SymfonyResponse::HTTP_OK);
        $response->assertCookie('downloadCompleteToken', 'abcdef123456', false);
    }

    public function testCaseIsSoftDeleted(): void
    {
        $user = $this->createUser([], 'compliance');
        $case = $this->createCaseForUser($user);

        $response = $this->actingAs($user)
            ->delete(route('api-access-requests-delete-case', $case->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);

        $this->assertTrue($case->refresh()->trashed());
    }

    public function testCaseIsStillAccessibleWhenSoftDeletedAndWithinPurgeWindow(): void
    {
        $user = $this->createUser([], 'compliance');
        $case = $this->createCaseForUser($user, [
            'deleted_at' => $this->faker->dateTimeBetween(
                sprintf('-%d days', PurgeSoftDeletedModels::PURGE_AFTER_DAYS - 1),
            ),
        ]);

        $response = $this->actingAs($user)
            ->get(route('api-access-requests-fragments-case', $case->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);
    }

    public function testDeleteTask(): void
    {
        CarbonImmutable::setTestNow('2021-07-12 08:45:00');

        $task = $this->createTaskForCase($this->case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->actingAs($this->user)->delete(route('api-access-requests-delete-task', $task->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);
        $response->assertJson([
            'uuid' => $task->uuid,
            'deleted_at' => '2021-07-12T08:45:00.000000Z',
        ]);

        $this->assertDatabaseHas('task', [
            'uuid' => $task->uuid,
            'deleted_at' => '2021-07-12 08:45:00',
        ]);

        // We can still access the task even if it's soft deleted.
        $response = $this->actingAs($this->user)->get(route('api-access-requests-fragments-task', $task->uuid));
        $response->assertJson([
            'data' => [
                'index' => [
                    'general' => [
                        'deletedAt' => null,
                    ],
                ],
                'task' => [
                    'general' => [
                        'deletedAt' => '2021-07-12T08:45:00Z',
                    ],
                ],
            ],
        ]);
    }

    public function testRestoreCase(): void
    {
        CarbonImmutable::setTestNow('2021-07-12 08:45:00');

        $response = $this->actingAs($this->user)->delete(route('api-access-requests-delete-case', $this->case->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $this->case->uuid,
            'deleted_at' => '2021-07-12 08:45:00',
        ]);

        $response = $this->post(route('api-access-requests-restore-case', $this->case->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $this->case->uuid,
            'deleted_at' => null,
        ]);
    }

    public function testRestoreTask(): void
    {
        CarbonImmutable::setTestNow('2021-07-12 08:45:00');

        $task = $this->createTaskForCase($this->case);

        $response = $this->actingAs($this->user)->delete(route('api-access-requests-delete-task', $task->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);

        $this->assertDatabaseHas('task', [
            'uuid' => $task->uuid,
            'deleted_at' => '2021-07-12 08:45:00',
        ]);

        $response = $this->post(route('api-access-requests-restore-task', $task->uuid));
        $response->assertStatus(SymfonyResponse::HTTP_OK);

        $this->assertDatabaseHas('task', [
            'uuid' => $task->uuid,
            'deleted_at' => null,
        ]);
    }

    private function mockGenerateIndexPdf(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2021, 5, 11, 15));

        $filename = 'inzageverzoek-2021-05-11-15:00:00.pdf';

        $mock = PDF::expects('loadView')
            ->andReturnSelf()
            ->getMock();

        $mock->expects('download')
            ->with($filename)
            ->andReturn(new Response('data', SymfonyResponse::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]));
    }

    private function mockGenerateTaskPdf(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::create(2021, 6, 16, 15));

        $filename = 'inzageverzoek-contact-2021-06-16-15:00:00.pdf';

        $mock = PDF::expects('loadView')
            ->andReturnSelf()
            ->getMock();

        $mock->expects('download')
            ->with($filename)
            ->andReturn(new Response('data', SymfonyResponse::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]));
    }
}

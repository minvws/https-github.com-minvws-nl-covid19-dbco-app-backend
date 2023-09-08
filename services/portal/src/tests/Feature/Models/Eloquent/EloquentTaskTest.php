<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Eloquent;

use App\Jobs\EloquentTaskSearchHashJob;
use App\Models\Eloquent\EloquentTask;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function json_decode;
use function sprintf;

class EloquentTaskTest extends FeatureTestCase
{
    public function testDataIsEncryptedJsonWithCorrectPrefix(): void
    {
        $label = $this->faker->word();

        $eloquentTask = $this->createTask([
            'created_at' => CarbonImmutable::now(),
            'label' => $label,
        ]);

        $databaseResult = DB::table('task')
            ->select('label')
            ->where('uuid', $eloquentTask->uuid)
            ->first();
        $this->assertEquals(
            sprintf('store:%s', CarbonImmutable::now()->format('Ymd')),
            json_decode($databaseResult->label)->key,
        );
    }

    #[Group('search-hash')]
    public function testCreatingTaskDispatchesSearchHashJobs(): void
    {
        Bus::fake();

        $task = EloquentTask::factory()->create();

        Bus::assertDispatched(static fn (EloquentTaskSearchHashJob $job): bool => $job->taskUuid === $task->uuid);
    }

    #[Group('search-hash')]
    public function testUpdatingTaskDispatchesSearchHashJobs(): void
    {
        $task = EloquentTask::factory()->create();

        Bus::fake();

        $task->touch();

        Bus::assertDispatched(static fn (EloquentTaskSearchHashJob $job): bool => $job->taskUuid === $task->uuid);
    }
}

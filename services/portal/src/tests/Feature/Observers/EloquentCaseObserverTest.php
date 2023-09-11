<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Jobs\ExportCaseToOsiris;
use App\Models\Enums\Osiris\CaseExportType;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;
use Tests\ModelCreator;

#[Group('osiris')]
final class EloquentCaseObserverTest extends FeatureTestCase
{
    use ModelCreator;

    public function testDeletedDispatchesOsirisExportJob(): void
    {
        Queue::fake();
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');

        $case = $this->createCase();
        $case->delete();

        Queue::assertPushed(
            ExportCaseToOsiris::class,
            static fn (ExportCaseToOsiris $job) => $job->caseExportType === CaseExportType::DELETED_STATUS,
        );
    }
}

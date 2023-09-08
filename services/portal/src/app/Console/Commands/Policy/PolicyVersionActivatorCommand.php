<?php

declare(strict_types=1);

namespace App\Console\Commands\Policy;

use App\Dto\Admin\UpdatePolicyVersionDto;
use App\Services\PolicyVersionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PhpOption\None;
use PhpOption\Some;

use function is_null;

class PolicyVersionActivatorCommand extends Command
{
    protected $signature = 'policy-version:activator:run';

    protected $description = 'Update status of PolicyVersions based on current status and startDate';

    public function handle(PolicyVersionService $policyVersionService): void
    {
        DB::transaction(
            function () use ($policyVersionService): void {
                $policyVersion = $policyVersionService->getPolicyVersionReadyForActivation();

                if (is_null($policyVersion)) {
                    return;
                }

                $dto = new UpdatePolicyVersionDto(
                    name: None::create(),
                    status: Some::create(PolicyVersionStatus::active()),
                    startDate: Some::create($policyVersion->start_date),
                );

                $policyVersionService->updatePolicyVersion($policyVersion, $dto);
                $this->info('PolicyVersion \'' . $policyVersion->name . '\' is activated!');
            },
        );
    }
}

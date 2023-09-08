<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Dto\Admin\CreatePolicyVersionDto;
use App\Models\Policy\PolicyVersion;
use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;

class PolicyVersionPopulator
{
    public function __construct(
        private readonly Connection $db,
        private readonly PolicyVersionRepository $policyVersionRepository,
    )
    {
    }

    public function populate(): PolicyVersion
    {
        return $this->db->transaction(
            fn (): PolicyVersion => $this->policyVersionRepository->createPolicyVersion(new CreatePolicyVersionDto(
                name: 'Default',
                startDate: CarbonImmutable::now(),
                status: PolicyVersionStatus::active(),
            )),
        );
    }
}

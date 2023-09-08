<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\PrincipalContextualSettings;
use Tests\Feature\FeatureTestCase;

class FragmentDataIsWrittenToCaseFragmentTableTest extends FeatureTestCase
{
    public function testRelationIsWrittenToCaseFragmentTable(): void
    {
        $case = $this->createCase([
            'principalContextualSettings' => PrincipalContextualSettings::getSchema()
                ->getVersion(1)
                ->getTestFactory()
                ->make(),
        ]);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
        ]);

        $this->assertdatabaseHas('case_fragment', [
            'fragment_name' => 'PrincipalContextualSettings',
            'case_uuid' => $case->uuid,
        ]);
    }
}

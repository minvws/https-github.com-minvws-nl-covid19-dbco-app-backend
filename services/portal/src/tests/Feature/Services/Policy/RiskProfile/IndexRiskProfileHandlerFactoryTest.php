<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\RiskProfile;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\RiskProfile;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use App\Services\Policy\RiskProfile\HospitalAdmittedHandler;
use App\Services\Policy\RiskProfile\IndexRiskProfileHandlerFactory;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('riskProfile')]
class IndexRiskProfileHandlerFactoryTest extends FeatureTestCase
{
    public function testCreate(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $riskProfile = RiskProfile::factory()->create([
            'risk_profile_enum' => IndexRiskProfile::hospitalAdmitted(),
        ]);

        $riskProfileHandler = IndexRiskProfileHandlerFactory::create($riskProfile);
        $this->assertInstanceOf(HospitalAdmittedHandler::class, $riskProfileHandler);
        $this->assertInstanceOf(PolicyGuidelineHandler::class, $riskProfileHandler->getPolicyGuidelineHandler());
    }
}

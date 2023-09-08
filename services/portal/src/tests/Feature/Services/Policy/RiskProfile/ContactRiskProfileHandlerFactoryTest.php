<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\RiskProfile;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\RiskProfile;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use App\Services\Policy\RiskProfile\ContactRiskProfileHandler;
use App\Services\Policy\RiskProfile\ContactRiskProfileHandlerFactory;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('riskProfile')]
class ContactRiskProfileHandlerFactoryTest extends FeatureTestCase
{
    public function testCreate(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $riskProfile = RiskProfile::factory()->create([
            'risk_profile_enum' => ContactRiskProfile::cat1VaccinatedDistanceNotPossible(),
        ]);

        $riskProfileHandler = ContactRiskProfileHandlerFactory::create($riskProfile);
        $this->assertInstanceOf(ContactRiskProfileHandler::class, $riskProfileHandler);
        $this->assertInstanceOf(PolicyGuidelineHandler::class, $riskProfileHandler->getPolicyGuidelineHandler());
    }
}

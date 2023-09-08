<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\RiskProfile;

use App\Events\PolicyVersionCreated;
use App\Exceptions\Policy\UnsupportedPolicyFactObjectHandlerException;
use App\Models\Policy\RiskProfile;
use App\Services\Policy\ContactPolicyFacts;
use App\Services\Policy\IndexPolicyFacts;
use App\Services\Policy\RiskProfile\ContactRiskProfileHandlerFactory;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('riskProfile')]
class ContactRiskProfileHandlerTest extends FeatureTestCase
{
    #[DataProvider('isApplicableDataProvider')]
    public function testIsApplicable(ContactRiskProfile $contactRiskProfileEnum, array $factsData): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $riskProfile = RiskProfile::factory()->create([
            'risk_profile_enum' => $contactRiskProfileEnum,
        ]);

        $contactRiskProfileHandler = ContactRiskProfileHandlerFactory::create($riskProfile);

        $facts = ContactPolicyFacts::create(
            $factsData['contactCategory'],
            $factsData['immunity'],
            $factsData['closeContactDuringQuarantine'],
        );

        $this->assertTrue($contactRiskProfileHandler->isApplicable($facts));
    }

    public static function isApplicableDataProvider(): array
    {
        return [
            'Categorie 1 - Wel gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat1VaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat1(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 1 - Wel gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat1VaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat1(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 1 - Niet gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat1NotVaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat1(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],

            'Categorie 1 - Niet gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat1NotVaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat1(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 1 - Vaccinatie onbekend - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat1VaccinationUnknownDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat1(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 1 - Vaccinatie onbekend - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat1VaccinationUnknownDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat1(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 2a - Wel gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat2VaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat2a(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 2a - Wel gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat2VaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat2a(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 2a - Niet gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat2NotVaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat2a(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],

            'Categorie 2a - Niet gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat2NotVaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat2a(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 2a - Vaccinatie onbekend - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat2VaccinationUnknownDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat2a(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 2a - Vaccinatie onbekend - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat2VaccinationUnknownDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat2a(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 2b - Wel gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat2VaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat2b(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 2b - Wel gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat2VaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat2b(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 2b - Niet gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat2NotVaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat2b(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],

            'Categorie 2b - Niet gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat2NotVaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat2b(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 2b - Vaccinatie onbekend - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat2VaccinationUnknownDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat2b(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 2b - Vaccinatie onbekend - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat2VaccinationUnknownDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat2b(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 3a - Wel gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat3VaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat3a(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 3a - Wel gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat3VaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat3a(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 3a - Niet gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat3NotVaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat3a(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],

            'Categorie 3a - Niet gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat3NotVaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat3a(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 3a - Vaccinatie onbekend - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat3VaccinationUnknownDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat3a(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 3a - Vaccinatie onbekend - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat3VaccinationUnknownDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat3a(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 3b - Wel gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat3VaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat3b(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 3b - Wel gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat3VaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat3b(),
                    'immunity' => YesNoUnknown::yes(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 3b - Niet gevaccineerd - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat3NotVaccinatedDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat3b(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],

            'Categorie 3b - Niet gevaccineerd - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat3NotVaccinatedDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat3b(),
                    'immunity' => YesNoUnknown::no(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
            'Categorie 3b - Vaccinatie onbekend - Afstand houden wel mogelijk' => [
                ContactRiskProfile::cat3VaccinationUnknownDistancePossible(),
                [
                    'contactCategory' => ContactCategory::cat3b(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::yes(),
                ],
            ],
            'Categorie 3b - Vaccinatie onbekend - Afstand houden niet mogelijk' => [
                ContactRiskProfile::cat3VaccinationUnknownDistanceNotPossible(),
                [
                    'contactCategory' => ContactCategory::cat3b(),
                    'immunity' => YesNoUnknown::unknown(),
                    'closeContactDuringQuarantine' => YesNoUnknown::no(),
                ],
            ],
        ];
    }

    public function testIsApplicableShouldThrowErrorIfUnsupported(): void
    {
        $this->expectException(UnsupportedPolicyFactObjectHandlerException::class);

        Event::fake([PolicyVersionCreated::class]);

        $riskProfile = RiskProfile::factory()->create([
            'risk_profile_enum' => ContactRiskProfile::cat1VaccinatedDistancePossible(),
        ]);

        $contactRiskProfileHandler = ContactRiskProfileHandlerFactory::create($riskProfile);

        $facts = IndexPolicyFacts::create(null, null, null, null);

        $contactRiskProfileHandler->isApplicable($facts);
    }
}

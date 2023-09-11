<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Intake;

use App\Mail\IntakeConfirmation;
use App\Services\Intake\IntakeConfirmationService;
use Illuminate\Support\Facades\Mail;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class IntakeConfirmationServiceTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function testConfirmToIndex(): void
    {
        $organisation = $this->createOrganisation();
        $intake = $this->createIntakeForOrganisationWithFragments($organisation, [], [
            'contact' => [
                'email' => 'foo@bar.com',
                'phone' => '06 1234 5678',
            ],
            'symptoms' => [
                'hasSymptoms' => YesNoUnknown::yes(),
                'symptoms' => [
                    Symptom::fever(),
                    Symptom::pain(),
                ],
            ],
            'test' => [
                'dateOfSymptomOnset' => '2020-01-01',
            ],
        ]);

        $intakeConfirmationService = $this->app->get(IntakeConfirmationService::class);
        $intakeConfirmationService->confirmToIndex($intake);

        Mail::assertSent(IntakeConfirmation::class, static function (IntakeConfirmation $mail) {
            return $mail->hasTo('foo@bar.com');
        });
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\IntakeConfirmation;
use App\Services\Policy\IndexPolicyGuidelineProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\NullLogger;
use Tests\Feature\FeatureTestCase;

use function config;
use function sprintf;

class IntakeConfirmationTest extends FeatureTestCase
{
    #[Group('policy')]
    #[Group('calendar')]
    public function testBuild(): void
    {
        config()->set('mail.mailers.zivver.from.address', 'intake@selfbco.nl');

        $organisation = $this->createOrganisation();
        $intake = $this->createIntakeForOrganisationWithFragments(
            $organisation,
            [
                'firstname' => 'foo',
                'prefix' => null,
                'lastname' => 'barbar',
            ],
            [
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
                'job' => [
                    'sectors' => [
                        '22', // verpleeghuisOfVerzorgingshuis
                    ],
                ],
                'test' => [
                    'dateOfSymptomOnset' => '2020-01-01',
                ],
            ],
        );

        $policyGuidelineProvider = $this->app->get(IndexPolicyGuidelineProvider::class);
        $intakeConfirmation = new IntakeConfirmation($intake, $policyGuidelineProvider);

        $configRepository = $this->app->get(Repository::class);
        $translator = $this->app->get(Translator::class);
        $intakeConfirmation->build($configRepository, new NullLogger(), $translator);
        $intakeConfirmation->hasFrom('intake@selfbco.nl', $organisation->name);
        $this->assertEquals('Persoonlijke adviezen op basis van je vragenlijst', $intakeConfirmation->subject);

        $intakeConfirmation->assertSeeInHtml('Heb je op maandag 6 januari nog wel klachten?');
        $intakeConfirmation->assertSeeInHtml('Informatie voor zorgprofessionals');
    }

    #[Group('policy')]
    #[DataProvider('buildNameDataProvider')]
    public function testBuildName(?string $firstname, ?string $prefix, ?string $lastname, string $expectedName): void
    {
        config()->set('mail.mailers.zivver.from.address', 'intake@selfbco.nl');

        $organisation = $this->createOrganisation();
        $intake = $this->createIntakeForOrganisationWithFragments(
            $organisation,
            [
                'firstname' => $firstname,
                'prefix' => $prefix,
                'lastname' => $lastname,
            ],
            [
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
            ],
        );

        $policyGuidelineProvider = $this->app->get(IndexPolicyGuidelineProvider::class);
        $intakeConfirmation = new IntakeConfirmation($intake, $policyGuidelineProvider);

        $configRepository = $this->app->get(Repository::class);
        $translator = $this->app->get(Translator::class);
        $intakeConfirmation->build($configRepository, new NullLogger(), $translator);

        $intakeConfirmation->assertSeeInHtml(sprintf('Beste %s,', $expectedName));
    }

    public static function buildNameDataProvider(): array
    {
        return [
            'full' => ['foo', 'von', 'barbar', 'foo von barbar'],
            'without firstname' => [null, 'von', 'barbar', 'von barbar'],
            'without prefix' => ['foo', null, 'barbar', 'foo barbar'],
            'without lastname' => ['foo', 'von', null, 'foo von'],
            'only firstname' => ['foo', null, null, 'foo'],
        ];
    }
}

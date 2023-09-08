<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Osiris;

use App\Console\Commands\Osiris\ReadOsirisXmlAnswers;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Services\Osiris\SoapMessage\SoapMessageBuilderFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function collect;

#[Group('osiris')]
class ReadOsirisXmlAnswersTest extends FeatureTestCase
{
    public function testItAlertsTheUserIfTheCaseDoesNotExist(): void
    {
        $this->artisan(ReadOsirisXmlAnswers::class, [
            'case' => '1234',
            'case-export-type' => $this->faker->randomElement(CaseExportType::cases())->value,
        ])
            ->expectsOutput('Case with uuid 1234 not found')
            ->assertExitCode(1);
    }

    public function testItShowsATableWithAllOsirisQuestionsForACase(): void
    {
        $case = $this->createCase();
        $caseExportType = $this->faker->randomElement([CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS]);
        $data = $this->getSortedAnswerArray($case, $caseExportType);

        $this->artisan(ReadOsirisXmlAnswers::class, [
            'case' => $case->uuid,
            'case-export-type' => $caseExportType->value,
        ])
            ->expectsTable(
                ['Code', 'Value'],
                $data,
            )
            ->assertExitCode(0);
    }

    public function testItFiltersTheTableWithAllOsirisQuestionsForACase(): void
    {
        $case = $this->createCase();
        $caseExportType = $this->faker->randomElement([CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS]);
        $data = $this->getSortedAnswerArray($case, $caseExportType);
        $data = [$data[0]];

        $this->artisan(ReadOsirisXmlAnswers::class, [
            'case' => $case->uuid,
            'case-export-type' => $caseExportType->value,
            'answer-code' => $data[0]['vraag_code'],
        ])
            ->expectsTable(
                ['Code', 'Value'],
                $data,
            )
            ->assertExitCode(0);
    }

    public function testItDisplaysAWarningWhenNoEntriesForGivenCode(): void
    {
        $case = $this->createCase();
        $this->artisan(ReadOsirisXmlAnswers::class, [
            'case' => $case->uuid,
            'case-export-type' => $this->faker->randomElement(CaseExportType::cases())->value,
            'answer-code' => $this->faker->word(),
        ])
            ->expectsOutput('There are no answers matching the given answer code')
            ->assertExitCode(1);
    }

    private function getSortedAnswerArray(EloquentCase $case, CaseExportType $caseExportType): Collection
    {
        /** @var SoapMessageBuilderFactory $factory */
        $factory = App::get(SoapMessageBuilderFactory::class);
        $soapMessageBody = $factory->build($case)
            ->makeSoapMessage($caseExportType)
            ->getBody();

        $answerData = collect([]);

        foreach ($soapMessageBody->antwoord as $child) {
            if (!(string) $child->vraag_code || !(string) $child->antwoord_tekst) {
                continue;
            }

            $answerData->add([
                'vraag_code' => (string) $child->vraag_code,
                'antwoord' => (string) $child->antwoord_tekst,
            ]);
        }

        return $answerData->sortBy('vraag_code');
    }
}

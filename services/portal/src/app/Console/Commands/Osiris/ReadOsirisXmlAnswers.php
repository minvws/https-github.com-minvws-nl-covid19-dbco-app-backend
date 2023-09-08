<?php

declare(strict_types=1);

namespace App\Console\Commands\Osiris;

use App\Console\Traits\WithTypedInput;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\CaseRepository;
use App\Services\Osiris\SoapMessage\SoapMessageBuilderFactory;
use Illuminate\Console\Command;

use function collect;
use function sprintf;
use function strtolower;

class ReadOsirisXmlAnswers extends Command
{
    use WithTypedInput;

    protected $signature = 'osiris:build-test-xml
                            {case : The uuid of the case}
                            {case-export-type : One of CaseExportType enum values}
                            {answer-code? : The answer code to get the value for}
    ';

    protected $description = 'Get the answer codes and values that would be sent to Osiris for a case';

    public function handle(CaseRepository $caseRepository, SoapMessageBuilderFactory $soapMessageBuilderFactory): int
    {
        $caseUuid = $this->getStringArgument('case');
        $case = $caseRepository->getCaseByUuid($caseUuid);

        if ($case === null) {
            $this->warn(sprintf('Case with uuid %s not found', $caseUuid));
            return self::FAILURE;
        }

        $answerCode = $this->getStringArgument('answer-code');
        $caseExportType = CaseExportType::from($this->getStringArgument('case-export-type'));

        $soapMessageBody = $soapMessageBuilderFactory->build($case)
            ->makeSoapMessage($caseExportType)
            ->getBody();

        $answerData = collect([]);

        foreach ($soapMessageBody->antwoord as $child) {
            if (!(string) $child->vraag_code || !(string) $child->antwoord_tekst) {
                continue;
            }


            if ($answerCode && strtolower($answerCode) !== strtolower((string) $child->vraag_code)) {
                continue;
            }

            $answerData->add([
                'vraag_code' => (string) $child->vraag_code,
                'antwoord' => (string) $child->antwoord_tekst,
            ]);
        }

        if (!empty($answerCode) && $answerData->count() === 0) {
            $this->error('There are no answers matching the given answer code');
            return self::FAILURE;
        }

        $answerData = $answerData->sortBy('vraag_code');

        $this->info(sprintf("Case %s has the following metadata:", $case->uuid));
        $this->table(['Property', 'Value'], [
            ['meld_nummer', (string) $soapMessageBody->meld_nummer ?: 'not set'],
            ['meld_code', (string) $soapMessageBody->meld_code ?: 'not set'],
            ['vragenlijst_versie', (string) $soapMessageBody->vragenlijst_versie ?: 'not set'],
            ['status_code', (string) $soapMessageBody->status_code ?: 'not set'],
            ['wis_missend_antwoord', (string) $soapMessageBody->wis_missend_antwoord ?: 'not set'],
            ['osiris_gebruiker_login', (string) $soapMessageBody->osiris_gebruiker_login ?: 'not set'],
        ]);

        $this->info('Case ' . $case->uuid . ' has the following answers:');
        $answerData->count() === 0
            ? $this->warn('No answers found')
            : $this->table(['Code', 'Value'], $answerData->toArray());

        return self::SUCCESS;
    }
}

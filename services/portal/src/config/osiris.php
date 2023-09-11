<?php

declare(strict_types=1);

use App\Models\Enums\Osiris\CaseExportType;
use App\Services\Osiris\Answer;
use App\Services\Osiris\SoapMessage\QuestionnaireVersion;

return [
    'questionnaire' => [
        'v10_start_date' => env('OSIRIS_V10_STARTDATE', '2022-06-01 00:00:00'),
        'answer_builders' => [
            Answer\MELGGDOntvDtBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\MELGGDExternBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS, CaseExportType::DELETED_STATUS],
            ],
            Answer\PATGeslachtBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVgebdatBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVpostcodeBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVwerk2wkBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVwerkzorgberBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVwerkand15mBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVwerkand15mberBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVStudentLLBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\PATAzcBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVwinstelBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVLastInf1ezktDtBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVwinsteltypeV3Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVwinsteltypeOvBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVpatvacV2Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVvacmerkLtsBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVpatvacLtsDtBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVDtbekvacLtsBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVvacmerkLtsandBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVHerTestBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVDtHerTestBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVmonnrBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVlabnaamBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVwinsteltypeV2Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVpatvacBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVNvacBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVherinfV3Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\VaccinationsBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVherinfV2Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVherinfmeldnrBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVVast1eziektedagBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\ZIE1eZiekteDtBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVdat1eposncovBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVtypeTestBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVtypeTestandBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVpatZhsBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVpatZhsIndBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVdat1ezkhopnBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVopnameICUBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVopnamedatumICUBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVgezstatBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\ZIEDtOverlijdenBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVondaandcomorBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVondaandcomorV2Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVondaandoverigBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVtrimzwangerBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\MERSPATbuitenlBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\TripsBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVEPIPatGerelBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\EPIPatMWbronBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVTypeContact1Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVHPZnr1Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVBcoTypeBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVBcoTypeandBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVNContactCat1Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVNContactCat2Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVCoronITMonnrBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9, QuestionnaireVersion::V10],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVContactInv3Builder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\SourceEnvironmentsBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
            Answer\NCOVsettingClusOmsBuilder::class => [
                'questionnaireVersion' => [QuestionnaireVersion::V9],
                'caseExportType' => [CaseExportType::INITIAL_ANSWERS, CaseExportType::DEFINITIVE_ANSWERS],
            ],
        ],
    ],
];

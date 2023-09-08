<?php

declare(strict_types=1);

namespace App\Dto\TestResultReport;

use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;

enum TypeOfTest: string
{
    case SELF_TEST = 'SARS-CoV-2 Zelftest';
    case LAB_TEST_PCR = 'SARS-CoV-2 PCR';
    case LAB_TEST_ANTIGEN = 'SARS-CoV-2 Antigeen';

    public static function toTestResultTypeOfTest(?TypeOfTest $self): TestResultTypeOfTest
    {
        // @codeCoverageIgnoreStart
        return match ($self) {
            null => TestResultTypeOfTest::unknown(),
            self::SELF_TEST => TestResultTypeOfTest::selftest(),
            self::LAB_TEST_PCR => TestResultTypeOfTest::pcr(),
            self::LAB_TEST_ANTIGEN => TestResultTypeOfTest::antigen(),
        };
        // @codeCoverageIgnoreEnd
    }
}

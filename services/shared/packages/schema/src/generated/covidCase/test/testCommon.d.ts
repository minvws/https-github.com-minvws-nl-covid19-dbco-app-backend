/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { InfectionIndicatorV1 } from '@dbco/enum';
import { LabTestIndicatorV1 } from '@dbco/enum';
import { SelfTestIndicatorV1 } from '@dbco/enum';
import { TestResultV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * TestCommon
 */
export interface TestCommon {
    dateOfSymptomOnset?: Date | null;
    isSymptomOnsetEstimated?: boolean | null;
    dateOfTest?: Date | null;
    dateOfResult?: Date | null;
    dateOfInfectiousnessStart?: Date | null;
    otherReason?: string | null;
    infectionIndicator?: InfectionIndicatorV1 | null;
    selfTestIndicator?: SelfTestIndicatorV1 | null;
    labTestIndicator?: LabTestIndicatorV1 | null;
    otherLabTestIndicator?: string | null;
    monsterNumber?: string | null;
    selfTestLabTestDate?: Date | null;
    selfTestLabTestResult?: TestResultV1 | null;
    isReinfection?: YesNoUnknownV1 | null;
    previousInfectionDateOfSymptom?: Date | null;
    previousInfectionSymptomFree?: boolean | null;
    previousInfectionProven?: YesNoUnknownV1 | null;
    contactOfConfirmedInfection?: boolean | null;
    previousInfectionReported?: YesNoUnknownV1 | null;
    source?: string | null;
    testLocation?: string | null;
    testLocationCategory?: string | null;
}

export type TestCommonDTO = DTO<TestCommon>;

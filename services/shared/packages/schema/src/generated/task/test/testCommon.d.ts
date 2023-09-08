/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TestResultV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * TestCommon
 */
export interface TestCommon {
    isTested?: YesNoUnknownV1 | null;
    testResult?: TestResultV1 | null;
    dateOfTest?: Date | null;
    isReinfection?: YesNoUnknownV1 | null;
    previousInfectionDateOfSymptom?: Date | null;
    previousInfectionReported?: YesNoUnknownV1 | null;
}

export type TestCommonDTO = DTO<TestCommon>;

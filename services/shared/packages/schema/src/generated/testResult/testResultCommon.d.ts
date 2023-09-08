/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { GeneralV1 } from '@dbco/schema/testResult/general/generalV1';
import { OrganisationV1 } from '@dbco/schema/organisation/organisationV1';
import { PersonV1 } from '@dbco/schema/person/personV1';
import { TestResultRawV1 } from '@dbco/schema/testResultRaw/testResultRawV1';
import { TestResultResultV1 } from '@dbco/enum';
import { TestResultSourceV1 } from '@dbco/enum';
import { TestResultTypeOfTestV1 } from '@dbco/enum';
import { TestResultTypeV1 } from '@dbco/enum';

/**
 * TestResultCommon
 */
export interface TestResultCommon {
    uuid: string;
    messageId: string;
    organisation: OrganisationV1;
    person?: PersonV1 | null;
    raw?: TestResultRawV1 | null;
    type: TestResultTypeV1;
    source: TestResultSourceV1;
    sourceId?: string | null;
    monsterNumber?: string | null;
    dateOfTest: Date;
    dateOfSymptomOnset?: Date | null;
    general: GeneralV1;
    receivedAt: Date;
    createdAt: Date;
    updatedAt: Date;
    typeOfTest?: TestResultTypeOfTestV1 | null;
    customTypeOfTest?: string | null;
    dateOfResult: Date;
    sampleLocation?: string | null;
    result: TestResultResultV1;
    laboratory?: string | null;
}

export type TestResultCommonDTO = DTO<TestResultCommon>;

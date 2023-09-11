/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CommunicationV1 } from '@dbco/schema/covidCase/communication/communicationV1';
import { GeneralV1 } from '@dbco/schema/covidCase/general/generalV1';
import { TestV1 } from '@dbco/schema/covidCase/test/testV1';

/**
 * CovidCaseV1UpTo2
 */
export interface CovidCaseV1UpTo2 {
    test: TestV1;
    general: GeneralV1;
    communication: CommunicationV1;
}

export type CovidCaseV1UpTo2DTO = DTO<CovidCaseV1UpTo2>;

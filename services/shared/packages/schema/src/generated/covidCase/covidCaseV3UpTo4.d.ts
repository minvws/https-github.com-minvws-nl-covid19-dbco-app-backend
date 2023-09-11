/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CommunicationV2 } from '@dbco/schema/covidCase/communication/communicationV2';
import { TestV2 } from '@dbco/schema/covidCase/test/testV2';

/**
 * CovidCaseV3UpTo4
 */
export interface CovidCaseV3UpTo4 {
    test: TestV2;
    communication: CommunicationV2;
}

export type CovidCaseV3UpTo4DTO = DTO<CovidCaseV3UpTo4>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { GeneralPractitionerAddressV1 } from '@dbco/schema/covidCase/generalPractitionerAddress/generalPractitionerAddressV1';

/**
 * GeneralPractitionerCommon
 */
export interface GeneralPractitionerCommon {
    name?: string | null;
    practiceName?: string | null;
    address?: GeneralPractitionerAddressV1 | null;
    hasInfectionNotificationConsent?: boolean | null;
}

export type GeneralPractitionerCommonDTO = DTO<GeneralPractitionerCommon>;

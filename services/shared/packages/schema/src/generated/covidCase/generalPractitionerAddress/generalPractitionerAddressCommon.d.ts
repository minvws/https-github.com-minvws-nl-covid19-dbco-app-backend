/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
/**
 * GeneralPractitionerAddressCommon
 */
export interface GeneralPractitionerAddressCommon {
    postalCode?: string | null;
    houseNumber?: string | null;
    houseNumberSuffix?: string | null;
    street?: string | null;
    town?: string | null;
}

export type GeneralPractitionerAddressCommonDTO = DTO<GeneralPractitionerAddressCommon>;

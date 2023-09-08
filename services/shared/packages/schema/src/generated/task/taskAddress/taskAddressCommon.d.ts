/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
/**
 * TaskAddressCommon
 */
export interface TaskAddressCommon {
    postalCode?: string | null;
    houseNumber?: string | null;
    houseNumberSuffix?: string | null;
    street?: string | null;
    town?: string | null;
}

export type TaskAddressCommonDTO = DTO<TaskAddressCommon>;

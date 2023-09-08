/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * GroupTransportCommon
 */
export interface GroupTransportCommon {
    withReservedSeats?: YesNoUnknownV1 | null;
}

export type GroupTransportCommonDTO = DTO<GroupTransportCommon>;

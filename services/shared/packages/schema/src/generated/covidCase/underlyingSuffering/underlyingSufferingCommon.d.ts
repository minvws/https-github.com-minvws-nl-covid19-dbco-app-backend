/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * UnderlyingSufferingCommon
 */
export interface UnderlyingSufferingCommon {
    hasUnderlyingSufferingOrMedication?: YesNoUnknownV1 | null;
    hasUnderlyingSuffering?: YesNoUnknownV1 | null;
    otherItems?: string[] | null;
    remarks?: string | null;
}

export type UnderlyingSufferingCommonDTO = DTO<UnderlyingSufferingCommon>;

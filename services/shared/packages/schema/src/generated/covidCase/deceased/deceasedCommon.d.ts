/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CauseOfDeathV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * DeceasedCommon
 */
export interface DeceasedCommon {
    isDeceased?: YesNoUnknownV1 | null;
    deceasedAt?: Date | null;
    cause?: CauseOfDeathV1 | null;
}

export type DeceasedCommonDTO = DTO<DeceasedCommon>;

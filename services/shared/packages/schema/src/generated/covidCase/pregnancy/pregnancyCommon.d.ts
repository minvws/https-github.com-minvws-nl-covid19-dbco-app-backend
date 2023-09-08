/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * PregnancyCommon
 */
export interface PregnancyCommon {
    isPregnant?: YesNoUnknownV1 | null;
}

export type PregnancyCommonDTO = DTO<PregnancyCommon>;

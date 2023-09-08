/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * RiskLocationV2Up
 */
export interface RiskLocationV2Up {
    hasRelatedSickness?: YesNoUnknownV1 | null;
    hasDifferentDiseaseCourse?: YesNoUnknownV1 | null;
}

export type RiskLocationV2UpDTO = DTO<RiskLocationV2Up>;

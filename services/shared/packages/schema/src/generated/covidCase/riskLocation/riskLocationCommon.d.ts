/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { RiskLocationTypeV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * RiskLocationCommon
 */
export interface RiskLocationCommon {
    isLivingAtRiskLocation?: YesNoUnknownV1 | null;
    type?: RiskLocationTypeV1 | null;
    otherType?: string | null;
}

export type RiskLocationCommonDTO = DTO<RiskLocationCommon>;

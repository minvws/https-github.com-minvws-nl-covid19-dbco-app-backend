/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
/**
 * MomentCommon
 */
export interface MomentCommon {
    day?: Date | null;
    startTime?: string | null;
    endTime?: string | null;
    source?: boolean | null;
    formatted?: string | null;
}

export type MomentCommonDTO = DTO<MomentCommon>;

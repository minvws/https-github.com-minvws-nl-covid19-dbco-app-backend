/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * JobCommon
 */
export interface JobCommon {
    worksInAviation?: YesNoUnknownV1 | null;
    worksInHealthCare?: YesNoUnknownV1 | null;
    healthCareFunction?: string | null;
}

export type JobCommonDTO = DTO<JobCommon>;

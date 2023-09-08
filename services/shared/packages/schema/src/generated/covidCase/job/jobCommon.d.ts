/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { JobSectorV1 } from '@dbco/enum';
import { ProfessionCareV1 } from '@dbco/enum';
import { ProfessionOtherV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * JobCommon
 */
export interface JobCommon {
    wasAtJob?: YesNoUnknownV1 | null;
    sectors?: JobSectorV1[] | null;
    professionCare?: ProfessionCareV1 | null;
    closeContactAtJob?: YesNoUnknownV1 | null;
    professionOther?: ProfessionOtherV1 | null;
    otherProfession?: string | null;
    particularities?: string | null;
}

export type JobCommonDTO = DTO<JobCommon>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TaskAdviceV2 } from '@dbco/enum';

/**
 * InformV2UpTo2
 */
export interface InformV2UpTo2 {
    advices?: TaskAdviceV2[] | null;
    vulnerableGroupsAdvice?: string | null;
}

export type InformV2UpTo2DTO = DTO<InformV2UpTo2>;

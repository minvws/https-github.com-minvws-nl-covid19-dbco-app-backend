/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TaskAdviceV1 } from '@dbco/enum';

/**
 * InformV1UpTo1
 */
export interface InformV1UpTo1 {
    advices?: TaskAdviceV1[] | null;
    testAdvice?: string | null;
    quarantineAdvice?: string | null;
}

export type InformV1UpTo1DTO = DTO<InformV1UpTo1>;

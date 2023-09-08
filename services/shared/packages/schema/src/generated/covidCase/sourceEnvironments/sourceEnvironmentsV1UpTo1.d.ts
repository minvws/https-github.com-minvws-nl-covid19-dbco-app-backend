/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ContextCategoryV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * SourceEnvironmentsV1UpTo1
 */
export interface SourceEnvironmentsV1UpTo1 {
    hasLikelySourceEnvironments?: YesNoUnknownV1 | null;
    likelySourceEnvironments?: ContextCategoryV1[] | null;
}

export type SourceEnvironmentsV1UpTo1DTO = DTO<SourceEnvironmentsV1UpTo1>;

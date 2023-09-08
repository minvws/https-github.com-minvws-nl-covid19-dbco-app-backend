/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { InformStatusV1 } from '@dbco/enum';
import { InformTargetV1 } from '@dbco/enum';
import { InformedByV1 } from '@dbco/enum';

/**
 * InformCommon
 */
export interface InformCommon {
    status?: InformStatusV1 | null;
    informedBy?: InformedByV1 | null;
    shareIndexNameWithContact?: boolean | null;
    informTarget?: InformTargetV1 | null;
    otherAdvice?: string | null;
}

export type InformCommonDTO = DTO<InformCommon>;

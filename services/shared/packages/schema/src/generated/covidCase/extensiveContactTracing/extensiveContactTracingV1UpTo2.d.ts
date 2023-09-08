/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ExtensiveContactTracingReasonV1 } from '@dbco/enum';

/**
 * ExtensiveContactTracingV1UpTo2
 */
export interface ExtensiveContactTracingV1UpTo2 {
    reasons?: ExtensiveContactTracingReasonV1[] | null;
    notes?: string | null;
}

export type ExtensiveContactTracingV1UpTo2DTO = DTO<ExtensiveContactTracingV1UpTo2>;

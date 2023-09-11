/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { BcoTypeV1 } from '@dbco/enum';

/**
 * ExtensiveContactTracingV2Up
 */
export interface ExtensiveContactTracingV2Up {
    receivesExtensiveContactTracing?: BcoTypeV1 | null;
    otherDescription?: string | null;
}

export type ExtensiveContactTracingV2UpDTO = DTO<ExtensiveContactTracingV2Up>;

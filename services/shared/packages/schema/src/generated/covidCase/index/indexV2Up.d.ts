/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { NoBsnOrAddressReasonV1 } from '@dbco/enum';

/**
 * IndexV2Up
 */
export interface IndexV2Up {
    hasNoBsnOrAddress?: NoBsnOrAddressReasonV1[] | null;
}

export type IndexV2UpDTO = DTO<IndexV2Up>;

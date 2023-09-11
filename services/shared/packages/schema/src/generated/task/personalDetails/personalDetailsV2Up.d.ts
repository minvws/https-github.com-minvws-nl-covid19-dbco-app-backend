/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { NoBsnOrAddressReasonV1 } from '@dbco/enum';

/**
 * PersonalDetailsV2Up
 */
export interface PersonalDetailsV2Up {
    hasNoBsnOrAddress?: NoBsnOrAddressReasonV1[] | null;
}

export type PersonalDetailsV2UpDTO = DTO<PersonalDetailsV2Up>;

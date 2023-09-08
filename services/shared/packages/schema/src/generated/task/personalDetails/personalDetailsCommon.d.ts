/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TaskAddressV1 } from '@dbco/schema/task/taskAddress/taskAddressV1';
import { GenderV1 } from '@dbco/enum';

/**
 * PersonalDetailsCommon
 */
export interface PersonalDetailsCommon {
    dateOfBirth?: Date | null;
    gender?: GenderV1 | null;
    address?: TaskAddressV1 | null;
    bsnCensored?: string | null;
    bsnLetters?: string | null;
    bsnNotes?: string | null;
}

export type PersonalDetailsCommonDTO = DTO<PersonalDetailsCommon>;

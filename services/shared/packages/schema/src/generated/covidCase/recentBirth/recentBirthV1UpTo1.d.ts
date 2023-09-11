/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * RecentBirthV1UpTo1
 */
export interface RecentBirthV1UpTo1 {
    hasRecentlyGivenBirth?: YesNoUnknownV1 | null;
    birthDate?: Date | null;
    birthRemarks?: string | null;
}

export type RecentBirthV1UpTo1DTO = DTO<RecentBirthV1UpTo1>;

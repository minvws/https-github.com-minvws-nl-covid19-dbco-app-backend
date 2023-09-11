/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { IndexAddressV1 } from '@dbco/schema/covidCase/indexAddress/indexAddressV1';
import { GenderV1 } from '@dbco/enum';

/**
 * IndexCommon
 */
export interface IndexCommon {
    initials?: string | null;
    firstname?: string | null;
    lastname?: string | null;
    bsnNotes?: string | null;
    dateOfBirth?: Date | null;
    gender?: GenderV1 | null;
    address?: IndexAddressV1 | null;
    bsnCensored?: string | null;
    bsnLetters?: string | null;
}

export type IndexCommonDTO = DTO<IndexCommon>;

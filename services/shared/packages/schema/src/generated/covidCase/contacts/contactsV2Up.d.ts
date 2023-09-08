/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * ContactsV2Up
 */
export interface ContactsV2Up {
    estimatedMissingContacts?: YesNoUnknownV1 | null;
    estimatedCategory1Contacts?: number | null;
    estimatedCategory2Contacts?: number | null;
}

export type ContactsV2UpDTO = DTO<ContactsV2Up>;

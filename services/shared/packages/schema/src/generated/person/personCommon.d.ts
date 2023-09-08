/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ContactDetailsV1 } from '@dbco/schema/person/contactDetails/contactDetailsV1';
import { NameAndAddressV1 } from '@dbco/schema/person/nameAndAddress/nameAndAddressV1';

/**
 * PersonCommon
 */
export interface PersonCommon {
    uuid: string;
    dateOfBirth: Date;
    nameAndAddress: NameAndAddressV1;
    contactDetails: ContactDetailsV1;
    pseudoBsnGuid?: string | null;
    searchNonBsn?: string | null;
    searchDateOfBirth?: string | null;
    searchPhone?: string | null;
    searchEmail?: string | null;
    createdAt: Date;
    updatedAt: Date;
}

export type PersonCommonDTO = DTO<PersonCommon>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ContactsV2 } from '@dbco/schema/covidCase/contacts/contactsV2';

/**
 * CovidCaseV2Up
 */
export interface CovidCaseV2Up {
    contacts: ContactsV2;
}

export type CovidCaseV2UpDTO = DTO<CovidCaseV2Up>;

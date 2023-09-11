/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ContactsV1 } from '@dbco/schema/covidCase/contacts/contactsV1';
import { ExtensiveContactTracingV1 } from '@dbco/schema/covidCase/extensiveContactTracing/extensiveContactTracingV1';
import { VaccinationV1 } from '@dbco/schema/covidCase/vaccination/vaccinationV1';

/**
 * CovidCaseV1UpTo1
 */
export interface CovidCaseV1UpTo1 {
    contacts: ContactsV1;
    extensiveContactTracing: ExtensiveContactTracingV1;
    vaccination: VaccinationV1;
}

export type CovidCaseV1UpTo1DTO = DTO<CovidCaseV1UpTo1>;

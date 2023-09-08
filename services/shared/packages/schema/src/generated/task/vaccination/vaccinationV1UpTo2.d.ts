/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { VaccineInjectionV1 } from '@dbco/schema/shared/vaccineInjection/vaccineInjectionV1';

/**
 * VaccinationV1UpTo2
 */
export interface VaccinationV1UpTo2 {
    vaccineInjections?: VaccineInjectionV1[] | null;
}

export type VaccinationV1UpTo2DTO = DTO<VaccinationV1UpTo2>;

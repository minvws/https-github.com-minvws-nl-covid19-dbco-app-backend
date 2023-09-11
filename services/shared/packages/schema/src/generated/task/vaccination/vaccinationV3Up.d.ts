/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { VaccineInjectionV1 } from '@dbco/schema/shared/vaccineInjection/vaccineInjectionV1';

/**
 * VaccinationV3Up
 */
export interface VaccinationV3Up {
    vaccineInjections?: VaccineInjectionV1[] | null;
    vaccinationCount?: number | null;
}

export type VaccinationV3UpDTO = DTO<VaccinationV3Up>;

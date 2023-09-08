/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { VaccineV1 } from '@dbco/enum';

/**
 * VaccineInjectionCommon
 */
export interface VaccineInjectionCommon {
    injectionDate?: Date | null;
    isInjectionDateEstimated?: boolean | null;
    vaccineType?: VaccineV1 | null;
    otherVaccineType?: string | null;
}

export type VaccineInjectionCommonDTO = DTO<VaccineInjectionCommon>;

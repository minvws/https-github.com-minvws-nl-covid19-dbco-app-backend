/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * VaccinationCommon
 */
export interface VaccinationCommon {
    isVaccinated?: YesNoUnknownV1 | null;
}

export type VaccinationCommonDTO = DTO<VaccinationCommon>;

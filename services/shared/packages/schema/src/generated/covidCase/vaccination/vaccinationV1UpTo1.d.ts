/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { VaccinationGroupV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * VaccinationV1UpTo1
 */
export interface VaccinationV1UpTo1 {
    hasCompletedVaccinationSeries?: boolean | null;
    hasReceivedInvite?: YesNoUnknownV1 | null;
    groups?: VaccinationGroupV1[] | null;
    otherGroup?: string | null;
}

export type VaccinationV1UpTo1DTO = DTO<VaccinationV1UpTo1>;

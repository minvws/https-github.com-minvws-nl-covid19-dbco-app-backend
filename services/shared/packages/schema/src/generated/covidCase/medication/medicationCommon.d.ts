/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { MedicineV1 } from '@dbco/schema/covidCase/medication/medicineV1';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * MedicationCommon
 */
export interface MedicationCommon {
    hasMedication?: YesNoUnknownV1 | null;
    isImmunoCompromised?: YesNoUnknownV1 | null;
    immunoCompromisedRemarks?: string | null;
    hasGivenPermission?: YesNoUnknownV1 | null;
    practitioner?: string | null;
    practitionerPhone?: string | null;
    hospitalName?: string | null;
    medicines?: MedicineV1[] | null;
}

export type MedicationCommonDTO = DTO<MedicationCommon>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CovidMeasureV1 } from '@dbco/enum';
import { PersonalProtectiveEquipmentV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * CircumstancesCommon
 */
export interface CircumstancesCommon {
    isUsingPPE?: YesNoUnknownV1 | null;
    ppeType?: string | null;
    usedPersonalProtectiveEquipment?: PersonalProtectiveEquipmentV1[] | null;
    ppeReplaceFrequency?: string | null;
    ppeMedicallyCompetent?: boolean | null;
    covidMeasures?: CovidMeasureV1[] | null;
    otherCovidMeasures?: string[] | null;
    causeForConcern?: YesNoUnknownV1 | null;
    causeForConcernRemark?: string | null;
    sharedTransportation?: boolean | null;
}

export type CircumstancesCommonDTO = DTO<CircumstancesCommon>;

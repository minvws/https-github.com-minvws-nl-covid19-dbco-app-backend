/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { PersonalProtectiveEquipmentV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * CircumstancesCommon
 */
export interface CircumstancesCommon {
    wasUsingPPE?: YesNoUnknownV1 | null;
    usedPersonalProtectiveEquipment?: PersonalProtectiveEquipmentV1[] | null;
    ppeType?: string | null;
    ppeReplaceFrequency?: string | null;
    ppeMedicallyCompetent?: boolean | null;
}

export type CircumstancesCommonDTO = DTO<CircumstancesCommon>;

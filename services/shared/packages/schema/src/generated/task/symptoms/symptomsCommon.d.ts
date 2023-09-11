/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { SymptomV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * SymptomsCommon
 */
export interface SymptomsCommon {
    hasSymptoms?: YesNoUnknownV1 | null;
    symptoms?: SymptomV1[] | null;
    otherSymptoms?: string[] | null;
    dateOfSymptomOnset?: Date | null;
}

export type SymptomsCommonDTO = DTO<SymptomsCommon>;

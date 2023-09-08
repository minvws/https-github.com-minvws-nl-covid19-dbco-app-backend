/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * SymptomsV1UpTo1
 */
export interface SymptomsV1UpTo1 {
    wasSymptomaticAtTimeOfCall?: YesNoUnknownV1 | null;
    stillHadSymptomsAt?: Date | null;
}

export type SymptomsV1UpTo1DTO = DTO<SymptomsV1UpTo1>;

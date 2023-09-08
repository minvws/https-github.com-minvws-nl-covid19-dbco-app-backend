/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { EduDaycareTypeV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * EduDaycareV1UpTo1
 */
export interface EduDaycareV1UpTo1 {
    isStudent?: YesNoUnknownV1 | null;
    type?: EduDaycareTypeV1 | null;
}

export type EduDaycareV1UpTo1DTO = DTO<EduDaycareV1UpTo1>;

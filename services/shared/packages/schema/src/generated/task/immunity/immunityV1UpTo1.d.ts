/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * ImmunityV1UpTo1
 */
export interface ImmunityV1UpTo1 {
    isImmune?: YesNoUnknownV1 | null;
    remarks?: string | null;
}

export type ImmunityV1UpTo1DTO = DTO<ImmunityV1UpTo1>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TripV1 } from '@dbco/schema/covidCase/trip/tripV1';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * AbroadCommon
 */
export interface AbroadCommon {
    wasAbroad?: YesNoUnknownV1 | null;
    trips?: TripV1[] | null;
}

export type AbroadCommonDTO = DTO<AbroadCommon>;

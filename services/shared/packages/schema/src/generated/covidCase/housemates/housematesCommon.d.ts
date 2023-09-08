/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * HousematesCommon
 */
export interface HousematesCommon {
    hasHouseMates?: YesNoUnknownV1 | null;
    hasOwnFacilities?: boolean | null;
    hasOwnKitchen?: boolean | null;
    hasOwnBedroom?: boolean | null;
    hasOwnRestroom?: boolean | null;
    canStrictlyIsolate?: boolean | null;
    bottlenecks?: string | null;
}

export type HousematesCommonDTO = DTO<HousematesCommon>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { AddressV1 } from '@dbco/schema/shared/address/addressV1';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * AlternateResidencyCommon
 */
export interface AlternateResidencyCommon {
    hasAlternateResidency?: YesNoUnknownV1 | null;
    address?: AddressV1 | null;
    remark?: string | null;
}

export type AlternateResidencyCommonDTO = DTO<AlternateResidencyCommon>;

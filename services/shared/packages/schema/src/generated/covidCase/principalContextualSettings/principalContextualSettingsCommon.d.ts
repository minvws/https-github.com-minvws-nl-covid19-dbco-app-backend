/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
/**
 * PrincipalContextualSettingsCommon
 */
export interface PrincipalContextualSettingsCommon {
    hasPrincipalContextualSettings?: boolean | null;
    items?: string[] | null;
    otherItems?: string[] | null;
}

export type PrincipalContextualSettingsCommonDTO = DTO<PrincipalContextualSettingsCommon>;

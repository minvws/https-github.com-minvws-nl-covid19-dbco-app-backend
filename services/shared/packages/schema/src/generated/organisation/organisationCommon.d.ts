/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { BcoPhaseV1 } from '@dbco/enum';

/**
 * OrganisationCommon
 */
export interface OrganisationCommon {
    uuid: string;
    name: string;
    abbreviation?: string | null;
    hpZoneCode?: string | null;
    bcoPhase?: BcoPhaseV1 | null;
}

export type OrganisationCommonDTO = DTO<OrganisationCommon>;

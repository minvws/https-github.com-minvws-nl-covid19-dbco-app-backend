/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { OrganisationV1 } from '@dbco/schema/organisation/organisationV1';

/**
 * CaseListCommon
 */
export interface CaseListCommon {
    uuid: string;
    name: string;
    isDefault?: boolean | null;
    isQueue?: boolean | null;
    organisation: OrganisationV1;
}

export type CaseListCommonDTO = DTO<CaseListCommon>;

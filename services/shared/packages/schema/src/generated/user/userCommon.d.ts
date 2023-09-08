/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { OrganisationV1 } from '@dbco/schema/organisation/organisationV1';

/**
 * UserCommon
 */
export interface UserCommon {
    uuid: string;
    name: string;
    roles?: string | null;
    organisations: OrganisationV1[];
}

export type UserCommonDTO = DTO<UserCommon>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { OrganisationV1 } from '@dbco/schema/organisation/organisationV1';

/**
 * EventCommon
 */
export interface EventCommon {
    uuid: string;
    organisation: OrganisationV1;
    type: string;
    createdAt: Date;
}

export type EventCommonDTO = DTO<EventCommon>;

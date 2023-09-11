/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { OrganisationV1 } from '@dbco/schema/organisation/organisationV1';
import { SectionV1 } from '@dbco/schema/section/sectionV1';

/**
 * PlaceCommon
 */
export interface PlaceCommon {
    uuid: string;
    createdAt: Date;
    updatedAt: Date;
    sections?: SectionV1[] | null;
    organisation: OrganisationV1;
    label: string;
    locationId?: string | null;
    category?: string | null;
    street?: string | null;
    housenumber?: string | null;
    housenumberSuffix?: string | null;
    postalcode?: string | null;
    town?: string | null;
    country: string;
    ggdCode?: string | null;
    ggdMunicipality?: string | null;
    isVerified: boolean;
}

export type PlaceCommonDTO = DTO<PlaceCommon>;

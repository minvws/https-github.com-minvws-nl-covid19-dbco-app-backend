/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CircumstancesV1 } from '@dbco/schema/context/circumstances/circumstancesV1';
import { ContactV1 } from '@dbco/schema/context/contact/contactV1';
import { GeneralV1 } from '@dbco/schema/context/general/generalV1';
import { SectionV1 } from '@dbco/schema/section/sectionV1';

/**
 * ContextCommon
 */
export interface ContextCommon {
    placeUuid?: string | null;
    uuid: string;
    sections?: SectionV1[] | null;
    general: GeneralV1;
    circumstances: CircumstancesV1;
    contact: ContactV1;
}

export type ContextCommonDTO = DTO<ContextCommon>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { MomentV1 } from '@dbco/schema/context/moment/momentV1';
import { ContextRelationshipV1 } from '@dbco/enum';

/**
 * GeneralCommon
 */
export interface GeneralCommon {
    label?: string | null;
    relationship?: ContextRelationshipV1 | null;
    otherRelationship?: string | null;
    remarks?: string | null;
    note?: string | null;
    isSource?: boolean | null;
    moments?: MomentV1[] | null;
}

export type GeneralCommonDTO = DTO<GeneralCommon>;

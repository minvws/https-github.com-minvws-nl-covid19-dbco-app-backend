/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ContactCategoryV1 } from '@dbco/enum';
import { RelationshipV1 } from '@dbco/enum';

/**
 * GeneralCommon
 */
export interface GeneralCommon {
    reference?: string | null;
    firstname?: string | null;
    lastname?: string | null;
    email?: string | null;
    phone?: string | null;
    deletedAt?: Date | null;
    dateOfLastExposure?: Date | null;
    category?: ContactCategoryV1 | null;
    isSource?: boolean | null;
    label?: string | null;
    context?: string | null;
    relationship?: RelationshipV1 | null;
    otherRelationship?: string | null;
    closeContactDuringQuarantine?: boolean | null;
    nature?: string | null;
    remarks?: string | null;
}

export type GeneralCommonDTO = DTO<GeneralCommon>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { GenderV1 } from '@dbco/enum';
import { RelationshipV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * AlternateContactCommon
 */
export interface AlternateContactCommon {
    hasAlternateContact?: YesNoUnknownV1 | null;
    gender?: GenderV1 | null;
    relationship?: RelationshipV1 | null;
    firstname?: string | null;
    lastname?: string | null;
    phone?: string | null;
    email?: string | null;
    isDefaultContact?: boolean | null;
}

export type AlternateContactCommonDTO = DTO<AlternateContactCommon>;

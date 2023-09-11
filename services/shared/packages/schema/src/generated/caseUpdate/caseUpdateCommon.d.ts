/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CaseUpdateContactV1 } from '@dbco/schema/caseUpdateContact/caseUpdateContactV1';
import { CaseUpdateFragmentV1 } from '@dbco/schema/caseUpdateFragment/caseUpdateFragmentV1';

/**
 * CaseUpdateCommon
 */
export interface CaseUpdateCommon {
    uuid: string;
    source: string;
    receivedAt: Date;
    createdAt: Date;
    fragments?: CaseUpdateFragmentV1[] | null;
    contacts?: CaseUpdateContactV1[] | null;
}

export type CaseUpdateCommonDTO = DTO<CaseUpdateCommon>;

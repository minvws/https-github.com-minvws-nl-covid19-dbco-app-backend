/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CaseUpdateContactFragmentV1 } from '@dbco/schema/caseUpdateContactFragment/caseUpdateContactFragmentV1';
import { TaskGroupV1 } from '@dbco/enum';

/**
 * CaseUpdateContactCommon
 */
export interface CaseUpdateContactCommon {
    uuid: string;
    contactGroup: TaskGroupV1;
    label?: string | null;
    fragments?: CaseUpdateContactFragmentV1[] | null;
}

export type CaseUpdateContactCommonDTO = DTO<CaseUpdateContactCommon>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CaseUpdateContactFragmentV1 } from '@dbco/schema/caseUpdateContactFragment/caseUpdateContactFragmentV1';

/**
 * IntakeContactCommon
 */
export interface IntakeContactCommon {
    uuid: string;
    type: string;
    fragments?: CaseUpdateContactFragmentV1[] | null;
    receivedAt: Date;
}

export type IntakeContactCommonDTO = DTO<IntakeContactCommon>;

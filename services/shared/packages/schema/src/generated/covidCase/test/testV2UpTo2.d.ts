/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TestReasonV2 } from '@dbco/enum';

/**
 * TestV2UpTo2
 */
export interface TestV2UpTo2 {
    reasons?: TestReasonV2[] | null;
}

export type TestV2UpTo2DTO = DTO<TestV2UpTo2>;

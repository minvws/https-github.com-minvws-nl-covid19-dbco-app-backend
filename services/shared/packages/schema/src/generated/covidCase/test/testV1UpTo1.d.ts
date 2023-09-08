/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TestReasonV1 } from '@dbco/enum';

/**
 * TestV1UpTo1
 */
export interface TestV1UpTo1 {
    reasons?: TestReasonV1[] | null;
}

export type TestV1UpTo1DTO = DTO<TestV1UpTo1>;

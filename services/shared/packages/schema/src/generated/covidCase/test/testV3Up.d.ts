/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TestReasonV3 } from '@dbco/enum';

/**
 * TestV3Up
 */
export interface TestV3Up {
    reasons?: TestReasonV3[] | null;
}

export type TestV3UpDTO = DTO<TestV3Up>;

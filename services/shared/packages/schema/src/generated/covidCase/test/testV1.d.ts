/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TestCommon } from './testCommon';
import { TestV1UpTo1 } from './testV1UpTo1';
import { TestV1UpTo3 } from './testV1UpTo3';

export type TestV1 = TestCommon & TestV1UpTo1 & TestV1UpTo3;

export type TestV1DTO = DTO<TestV1>;

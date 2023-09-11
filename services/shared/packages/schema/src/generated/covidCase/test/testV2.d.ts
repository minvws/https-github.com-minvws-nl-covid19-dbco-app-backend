/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TestCommon } from './testCommon';
import { TestV1UpTo3 } from './testV1UpTo3';
import { TestV2Up } from './testV2Up';
import { TestV2UpTo2 } from './testV2UpTo2';

export type TestV2 = TestCommon & TestV1UpTo3 & TestV2Up & TestV2UpTo2;

export type TestV2DTO = DTO<TestV2>;

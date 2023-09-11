/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TestCommon } from './testCommon';
import { TestV2Up } from './testV2Up';
import { TestV3Up } from './testV3Up';
import { TestV4Up } from './testV4Up';

export type TestV4 = TestCommon & TestV2Up & TestV3Up & TestV4Up;

export type TestV4DTO = DTO<TestV4>;

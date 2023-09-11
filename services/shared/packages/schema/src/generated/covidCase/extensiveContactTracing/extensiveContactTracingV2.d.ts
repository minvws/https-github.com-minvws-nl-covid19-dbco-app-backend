/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ExtensiveContactTracingCommon } from './extensiveContactTracingCommon';
import { ExtensiveContactTracingV1UpTo2 } from './extensiveContactTracingV1UpTo2';
import { ExtensiveContactTracingV2Up } from './extensiveContactTracingV2Up';

export type ExtensiveContactTracingV2 = ExtensiveContactTracingCommon & ExtensiveContactTracingV1UpTo2 & ExtensiveContactTracingV2Up;

export type ExtensiveContactTracingV2DTO = DTO<ExtensiveContactTracingV2>;

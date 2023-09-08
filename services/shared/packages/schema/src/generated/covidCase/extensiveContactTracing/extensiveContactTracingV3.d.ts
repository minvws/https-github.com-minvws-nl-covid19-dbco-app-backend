/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ExtensiveContactTracingCommon } from './extensiveContactTracingCommon';
import { ExtensiveContactTracingV2Up } from './extensiveContactTracingV2Up';
import { ExtensiveContactTracingV3Up } from './extensiveContactTracingV3Up';

export type ExtensiveContactTracingV3 = ExtensiveContactTracingCommon & ExtensiveContactTracingV2Up & ExtensiveContactTracingV3Up;

export type ExtensiveContactTracingV3DTO = DTO<ExtensiveContactTracingV3>;

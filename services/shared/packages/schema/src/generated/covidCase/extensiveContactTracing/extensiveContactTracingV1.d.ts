/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ExtensiveContactTracingCommon } from './extensiveContactTracingCommon';
import { ExtensiveContactTracingV1UpTo1 } from './extensiveContactTracingV1UpTo1';
import { ExtensiveContactTracingV1UpTo2 } from './extensiveContactTracingV1UpTo2';

export type ExtensiveContactTracingV1 = ExtensiveContactTracingCommon & ExtensiveContactTracingV1UpTo1 & ExtensiveContactTracingV1UpTo2;

export type ExtensiveContactTracingV1DTO = DTO<ExtensiveContactTracingV1>;

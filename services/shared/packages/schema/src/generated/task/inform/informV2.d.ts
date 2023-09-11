/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { InformCommon } from './informCommon';
import { InformV2Up } from './informV2Up';
import { InformV2UpTo2 } from './informV2UpTo2';

export type InformV2 = InformCommon & InformV2Up & InformV2UpTo2;

export type InformV2DTO = DTO<InformV2>;

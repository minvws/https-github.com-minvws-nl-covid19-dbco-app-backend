/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { InformCommon } from './informCommon';
import { InformV2Up } from './informV2Up';
import { InformV3Up } from './informV3Up';

export type InformV3 = InformCommon & InformV2Up & InformV3Up;

export type InformV3DTO = DTO<InformV3>;

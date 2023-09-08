/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TaskCommon } from './taskCommon';
import { TaskV1UpTo4 } from './taskV1UpTo4';
import { TaskV1UpTo5 } from './taskV1UpTo5';
import { TaskV2Up } from './taskV2Up';
import { TaskV2UpTo3 } from './taskV2UpTo3';
import { TaskV3Up } from './taskV3Up';
import { TaskV3UpTo6 } from './taskV3UpTo6';

export type TaskV3 = TaskCommon & TaskV1UpTo4 & TaskV1UpTo5 & TaskV2Up & TaskV2UpTo3 & TaskV3Up & TaskV3UpTo6;

export type TaskV3DTO = DTO<TaskV3>;

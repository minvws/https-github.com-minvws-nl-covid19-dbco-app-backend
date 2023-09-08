/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TaskCommon } from './taskCommon';
import { TaskV1UpTo5 } from './taskV1UpTo5';
import { TaskV2Up } from './taskV2Up';
import { TaskV3Up } from './taskV3Up';
import { TaskV3UpTo6 } from './taskV3UpTo6';
import { TaskV4Up } from './taskV4Up';
import { TaskV5Up } from './taskV5Up';

export type TaskV5 = TaskCommon & TaskV1UpTo5 & TaskV2Up & TaskV3Up & TaskV3UpTo6 & TaskV4Up & TaskV5Up;

export type TaskV5DTO = DTO<TaskV5>;

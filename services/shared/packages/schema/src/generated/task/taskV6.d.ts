/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TaskCommon } from './taskCommon';
import { TaskV2Up } from './taskV2Up';
import { TaskV3Up } from './taskV3Up';
import { TaskV3UpTo6 } from './taskV3UpTo6';
import { TaskV4Up } from './taskV4Up';
import { TaskV5Up } from './taskV5Up';
import { TaskV6Up } from './taskV6Up';

export type TaskV6 = TaskCommon & TaskV2Up & TaskV3Up & TaskV3UpTo6 & TaskV4Up & TaskV5Up & TaskV6Up;

export type TaskV6DTO = DTO<TaskV6>;

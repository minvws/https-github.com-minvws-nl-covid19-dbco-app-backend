/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TaskCommon } from './taskCommon';
import { TaskV2Up } from './taskV2Up';
import { TaskV3Up } from './taskV3Up';
import { TaskV4Up } from './taskV4Up';
import { TaskV5Up } from './taskV5Up';
import { TaskV6Up } from './taskV6Up';
import { TaskV7Up } from './taskV7Up';

export type TaskV7 = TaskCommon & TaskV2Up & TaskV3Up & TaskV4Up & TaskV5Up & TaskV6Up & TaskV7Up;

export type TaskV7DTO = DTO<TaskV7>;

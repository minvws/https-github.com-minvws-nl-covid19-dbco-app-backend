/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { TaskCommon } from './taskCommon';
import { TaskV1UpTo1 } from './taskV1UpTo1';
import { TaskV1UpTo2 } from './taskV1UpTo2';
import { TaskV1UpTo4 } from './taskV1UpTo4';
import { TaskV1UpTo5 } from './taskV1UpTo5';

export type TaskV1 = TaskCommon & TaskV1UpTo1 & TaskV1UpTo2 & TaskV1UpTo4 & TaskV1UpTo5;

export type TaskV1DTO = DTO<TaskV1>;

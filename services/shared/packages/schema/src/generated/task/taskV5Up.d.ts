/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ImmunityV2 } from '@dbco/schema/task/immunity/immunityV2';
import { PersonalDetailsV2 } from '@dbco/schema/task/personalDetails/personalDetailsV2';

/**
 * TaskV5Up
 */
export interface TaskV5Up {
    personalDetails: PersonalDetailsV2;
    immunity: ImmunityV2;
}

export type TaskV5UpDTO = DTO<TaskV5Up>;

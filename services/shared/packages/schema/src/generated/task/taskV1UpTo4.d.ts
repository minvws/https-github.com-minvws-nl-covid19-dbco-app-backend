/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { ImmunityV1 } from '@dbco/schema/task/immunity/immunityV1';
import { PersonalDetailsV1 } from '@dbco/schema/task/personalDetails/personalDetailsV1';

/**
 * TaskV1UpTo4
 */
export interface TaskV1UpTo4 {
    personalDetails: PersonalDetailsV1;
    immunity: ImmunityV1;
}

export type TaskV1UpTo4DTO = DTO<TaskV1UpTo4>;

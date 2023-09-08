/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { VaccinationV3 } from '@dbco/schema/task/vaccination/vaccinationV3';

/**
 * TaskV4Up
 */
export interface TaskV4Up {
    vaccination: VaccinationV3;
}

export type TaskV4UpDTO = DTO<TaskV4Up>;

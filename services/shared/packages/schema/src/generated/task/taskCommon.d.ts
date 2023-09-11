/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { AlternateContactV1 } from '@dbco/schema/task/alternateContact/alternateContactV1';
import { AlternativeLanguageV1 } from '@dbco/schema/task/alternativeLanguage/alternativeLanguageV1';
import { CircumstancesV1 } from '@dbco/schema/task/circumstances/circumstancesV1';
import { GeneralV1 } from '@dbco/schema/task/general/generalV1';
import { JobV1 } from '@dbco/schema/task/job/jobV1';
import { SymptomsV1 } from '@dbco/schema/task/symptoms/symptomsV1';
import { TaskGroupV1 } from '@dbco/enum';

/**
 * TaskCommon
 */
export interface TaskCommon {
    uuid: string;
    taskGroup: TaskGroupV1;
    general: GeneralV1;
    job: JobV1;
    symptoms: SymptomsV1;
    circumstances: CircumstancesV1;
    alternateContact: AlternateContactV1;
    alternativeLanguage: AlternativeLanguageV1;
}

export type TaskCommonDTO = DTO<TaskCommon>;

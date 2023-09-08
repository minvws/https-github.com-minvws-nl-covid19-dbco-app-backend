import type { CovidCaseV1, CovidCaseV1DTO } from '@dbco/schema/covidCase/covidCaseV1';
import type { CovidCaseV2, CovidCaseV2DTO } from '@dbco/schema/covidCase/covidCaseV2';
import type { CovidCaseV3, CovidCaseV3DTO } from '@dbco/schema/covidCase/covidCaseV3';
import type { CovidCaseV4, CovidCaseV4DTO } from '@dbco/schema/covidCase/covidCaseV4';
import type { CovidCaseV5, CovidCaseV5DTO } from '@dbco/schema/covidCase/covidCaseV5';
import type { CovidCaseV6, CovidCaseV6DTO } from '@dbco/schema/covidCase/covidCaseV6';
import type { CovidCaseV7, CovidCaseV7DTO } from '@dbco/schema/covidCase/covidCaseV7';
import type { CovidCaseV8, CovidCaseV8DTO } from '@dbco/schema/covidCase/covidCaseV8';

import type { TaskV1, TaskV1DTO } from '@dbco/schema/task/taskV1';
import type { TaskV2, TaskV2DTO } from '@dbco/schema/task/taskV2';
import type { TaskV3, TaskV3DTO } from '@dbco/schema/task/taskV3';
import type { TaskV4, TaskV4DTO } from '@dbco/schema/task/taskV4';
import type { TaskV5, TaskV5DTO } from '@dbco/schema/task/taskV5';
import type { TaskV6, TaskV6DTO } from '@dbco/schema/task/taskV6';
import type { TaskV7, TaskV7DTO } from '@dbco/schema/task/taskV7';

export type CovidCaseUnion =
    | CovidCaseV1
    | CovidCaseV2
    | CovidCaseV3
    | CovidCaseV4
    | CovidCaseV5
    | CovidCaseV6
    | CovidCaseV7
    | CovidCaseV8;

export type TaskUnion = TaskV1 | TaskV2 | TaskV3 | TaskV4 | TaskV5 | TaskV6 | TaskV7;

export type CovidCaseUnionDTO =
    | CovidCaseV1DTO
    | CovidCaseV2DTO
    | CovidCaseV3DTO
    | CovidCaseV4DTO
    | CovidCaseV5DTO
    | CovidCaseV6DTO
    | CovidCaseV7DTO
    | CovidCaseV8DTO;

export type TaskUnionDTO = TaskV1DTO | TaskV2DTO | TaskV3DTO | TaskV4DTO | TaskV5DTO | TaskV6DTO | TaskV7DTO;

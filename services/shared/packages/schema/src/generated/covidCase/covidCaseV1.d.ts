/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CovidCaseCommon } from './covidCaseCommon';
import { CovidCaseV1UpTo1 } from './covidCaseV1UpTo1';
import { CovidCaseV1UpTo2 } from './covidCaseV1UpTo2';
import { CovidCaseV1UpTo3 } from './covidCaseV1UpTo3';
import { CovidCaseV1UpTo4 } from './covidCaseV1UpTo4';
import { CovidCaseV1UpTo6 } from './covidCaseV1UpTo6';

export type CovidCaseV1 = CovidCaseCommon & CovidCaseV1UpTo1 & CovidCaseV1UpTo2 & CovidCaseV1UpTo3 & CovidCaseV1UpTo4 & CovidCaseV1UpTo6;

export type CovidCaseV1DTO = DTO<CovidCaseV1>;

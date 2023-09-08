/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CovidCaseCommon } from './covidCaseCommon';
import { CovidCaseV1UpTo3 } from './covidCaseV1UpTo3';
import { CovidCaseV1UpTo4 } from './covidCaseV1UpTo4';
import { CovidCaseV1UpTo6 } from './covidCaseV1UpTo6';
import { CovidCaseV2Up } from './covidCaseV2Up';
import { CovidCaseV2UpTo3 } from './covidCaseV2UpTo3';
import { CovidCaseV2UpTo4 } from './covidCaseV2UpTo4';
import { CovidCaseV3Up } from './covidCaseV3Up';
import { CovidCaseV3UpTo3 } from './covidCaseV3UpTo3';
import { CovidCaseV3UpTo4 } from './covidCaseV3UpTo4';

export type CovidCaseV3 = CovidCaseCommon & CovidCaseV1UpTo3 & CovidCaseV1UpTo4 & CovidCaseV1UpTo6 & CovidCaseV2Up & CovidCaseV2UpTo3 & CovidCaseV2UpTo4 & CovidCaseV3Up & CovidCaseV3UpTo3 & CovidCaseV3UpTo4;

export type CovidCaseV3DTO = DTO<CovidCaseV3>;

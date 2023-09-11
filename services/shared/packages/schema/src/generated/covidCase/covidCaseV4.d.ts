/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CovidCaseCommon } from './covidCaseCommon';
import { CovidCaseV1UpTo4 } from './covidCaseV1UpTo4';
import { CovidCaseV1UpTo6 } from './covidCaseV1UpTo6';
import { CovidCaseV2Up } from './covidCaseV2Up';
import { CovidCaseV2UpTo4 } from './covidCaseV2UpTo4';
import { CovidCaseV3Up } from './covidCaseV3Up';
import { CovidCaseV3UpTo4 } from './covidCaseV3UpTo4';
import { CovidCaseV4Up } from './covidCaseV4Up';
import { CovidCaseV4UpTo4 } from './covidCaseV4UpTo4';

export type CovidCaseV4 = CovidCaseCommon & CovidCaseV1UpTo4 & CovidCaseV1UpTo6 & CovidCaseV2Up & CovidCaseV2UpTo4 & CovidCaseV3Up & CovidCaseV3UpTo4 & CovidCaseV4Up & CovidCaseV4UpTo4;

export type CovidCaseV4DTO = DTO<CovidCaseV4>;

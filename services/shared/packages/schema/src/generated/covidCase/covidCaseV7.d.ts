/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CovidCaseCommon } from './covidCaseCommon';
import { CovidCaseV2Up } from './covidCaseV2Up';
import { CovidCaseV3Up } from './covidCaseV3Up';
import { CovidCaseV4Up } from './covidCaseV4Up';
import { CovidCaseV5Up } from './covidCaseV5Up';
import { CovidCaseV6Up } from './covidCaseV6Up';
import { CovidCaseV7Up } from './covidCaseV7Up';

export type CovidCaseV7 = CovidCaseCommon & CovidCaseV2Up & CovidCaseV3Up & CovidCaseV4Up & CovidCaseV5Up & CovidCaseV6Up & CovidCaseV7Up;

export type CovidCaseV7DTO = DTO<CovidCaseV7>;

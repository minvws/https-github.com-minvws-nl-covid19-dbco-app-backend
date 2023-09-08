/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { SymptomsV2 } from '@dbco/schema/covidCase/symptoms/symptomsV2';

/**
 * CovidCaseV7Up
 */
export interface CovidCaseV7Up {
    symptoms: SymptomsV2;
}

export type CovidCaseV7UpDTO = DTO<CovidCaseV7Up>;

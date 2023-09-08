/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { UnderlyingSufferingV2 } from '@dbco/schema/covidCase/underlyingSuffering/underlyingSufferingV2';
import { VaccinationV3 } from '@dbco/schema/covidCase/vaccination/vaccinationV3';

/**
 * CovidCaseV4Up
 */
export interface CovidCaseV4Up {
    vaccination: VaccinationV3;
    underlyingSuffering: UnderlyingSufferingV2;
}

export type CovidCaseV4UpDTO = DTO<CovidCaseV4Up>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { VaccinationCommon } from './vaccinationCommon';
import { VaccinationV1UpTo2 } from './vaccinationV1UpTo2';
import { VaccinationV2Up } from './vaccinationV2Up';

export type VaccinationV2 = VaccinationCommon & VaccinationV1UpTo2 & VaccinationV2Up;

export type VaccinationV2DTO = DTO<VaccinationV2>;

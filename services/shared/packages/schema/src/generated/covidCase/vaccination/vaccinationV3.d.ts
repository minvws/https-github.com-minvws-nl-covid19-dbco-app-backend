/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { VaccinationCommon } from './vaccinationCommon';
import { VaccinationV2Up } from './vaccinationV2Up';
import { VaccinationV3Up } from './vaccinationV3Up';

export type VaccinationV3 = VaccinationCommon & VaccinationV2Up & VaccinationV3Up;

export type VaccinationV3DTO = DTO<VaccinationV3>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { VaccinationCommon } from './vaccinationCommon';
import { VaccinationV1UpTo1 } from './vaccinationV1UpTo1';
import { VaccinationV1UpTo2 } from './vaccinationV1UpTo2';

export type VaccinationV1 = VaccinationCommon & VaccinationV1UpTo1 & VaccinationV1UpTo2;

export type VaccinationV1DTO = DTO<VaccinationV1>;

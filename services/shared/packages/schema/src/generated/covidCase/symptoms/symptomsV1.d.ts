/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { SymptomsCommon } from './symptomsCommon';
import { SymptomsV1UpTo1 } from './symptomsV1UpTo1';

export type SymptomsV1 = SymptomsCommon & SymptomsV1UpTo1;

export type SymptomsV1DTO = DTO<SymptomsV1>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { PersonalDetailsCommon } from './personalDetailsCommon';
import { PersonalDetailsV2Up } from './personalDetailsV2Up';

export type PersonalDetailsV2 = PersonalDetailsCommon & PersonalDetailsV2Up;

export type PersonalDetailsV2DTO = DTO<PersonalDetailsV2>;

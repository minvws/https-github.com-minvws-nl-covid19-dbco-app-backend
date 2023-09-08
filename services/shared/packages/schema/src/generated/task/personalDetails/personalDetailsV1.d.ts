/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { PersonalDetailsCommon } from './personalDetailsCommon';
import { PersonalDetailsV1UpTo1 } from './personalDetailsV1UpTo1';

export type PersonalDetailsV1 = PersonalDetailsCommon & PersonalDetailsV1UpTo1;

export type PersonalDetailsV1DTO = DTO<PersonalDetailsV1>;

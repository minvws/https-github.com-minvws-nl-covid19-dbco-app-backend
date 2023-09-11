/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CommunicationCommon } from './communicationCommon';
import { CommunicationV1UpTo1 } from './communicationV1UpTo1';
import { CommunicationV1UpTo3 } from './communicationV1UpTo3';

export type CommunicationV1 = CommunicationCommon & CommunicationV1UpTo1 & CommunicationV1UpTo3;

export type CommunicationV1DTO = DTO<CommunicationV1>;

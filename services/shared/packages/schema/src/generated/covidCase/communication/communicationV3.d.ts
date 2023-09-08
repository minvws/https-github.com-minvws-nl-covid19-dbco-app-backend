/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CommunicationCommon } from './communicationCommon';
import { CommunicationV1UpTo3 } from './communicationV1UpTo3';
import { CommunicationV2Up } from './communicationV2Up';
import { CommunicationV2UpTo3 } from './communicationV2UpTo3';
import { CommunicationV3Up } from './communicationV3Up';

export type CommunicationV3 = CommunicationCommon & CommunicationV1UpTo3 & CommunicationV2Up & CommunicationV2UpTo3 & CommunicationV3Up;

export type CommunicationV3DTO = DTO<CommunicationV3>;

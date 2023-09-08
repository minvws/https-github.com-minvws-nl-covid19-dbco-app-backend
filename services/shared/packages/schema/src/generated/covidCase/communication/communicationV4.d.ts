/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CommunicationCommon } from './communicationCommon';
import { CommunicationV2Up } from './communicationV2Up';
import { CommunicationV3Up } from './communicationV3Up';
import { CommunicationV4Up } from './communicationV4Up';

export type CommunicationV4 = CommunicationCommon & CommunicationV2Up & CommunicationV3Up & CommunicationV4Up;

export type CommunicationV4DTO = DTO<CommunicationV4>;

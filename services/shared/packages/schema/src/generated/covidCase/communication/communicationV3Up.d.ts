/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * CommunicationV3Up
 */
export interface CommunicationV3Up {
    scientificResearchConsent?: YesNoUnknownV1 | null;
    remarksRivm?: string | null;
}

export type CommunicationV3UpDTO = DTO<CommunicationV3Up>;

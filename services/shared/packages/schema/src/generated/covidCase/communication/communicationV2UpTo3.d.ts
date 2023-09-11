/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { IsolationAdviceV2 } from '@dbco/enum';

/**
 * CommunicationV2UpTo3
 */
export interface CommunicationV2UpTo3 {
    isolationAdviceGiven?: IsolationAdviceV2[] | null;
}

export type CommunicationV2UpTo3DTO = DTO<CommunicationV2UpTo3>;

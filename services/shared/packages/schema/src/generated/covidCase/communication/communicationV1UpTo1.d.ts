/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { IsolationAdviceV1 } from '@dbco/enum';

/**
 * CommunicationV1UpTo1
 */
export interface CommunicationV1UpTo1 {
    isolationAdviceGiven?: IsolationAdviceV1[] | null;
}

export type CommunicationV1UpTo1DTO = DTO<CommunicationV1UpTo1>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * ContactCommon
 */
export interface ContactCommon {
    firstname?: string | null;
    lastname?: string | null;
    phone?: string | null;
    isInformed?: boolean | null;
    notificationConsent?: YesNoUnknownV1 | null;
    notificationNamedConsent?: boolean | null;
}

export type ContactCommonDTO = DTO<ContactCommon>;

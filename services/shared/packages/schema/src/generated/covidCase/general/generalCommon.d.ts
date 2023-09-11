/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
/**
 * GeneralCommon
 */
export interface GeneralCommon {
    source?: string | null;
    reference?: string | null;
    hpzoneNumber?: string | null;
    notes?: string | null;
    organisation?: any | null;
    createdAt?: Date | null;
    deletedAt?: Date | null;
    pairingAllowedUntil?: Date | null;
    isPairingAllowed?: boolean | null;
    expiresAt?: Date | null;
}

export type GeneralCommonDTO = DTO<GeneralCommon>;

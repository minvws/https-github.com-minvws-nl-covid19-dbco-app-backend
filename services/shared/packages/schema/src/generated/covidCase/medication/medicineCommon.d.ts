/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
/**
 * MedicineCommon
 */
export interface MedicineCommon {
    name: string;
    remark?: string | null;
    knownEffects?: string | null;
}

export type MedicineCommonDTO = DTO<MedicineCommon>;

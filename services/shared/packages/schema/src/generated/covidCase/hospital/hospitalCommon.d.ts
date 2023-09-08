/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { HospitalReasonV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * HospitalCommon
 */
export interface HospitalCommon {
    isAdmitted?: YesNoUnknownV1 | null;
    name?: string | null;
    location?: string | null;
    admittedAt?: Date | null;
    releasedAt?: Date | null;
    reason?: HospitalReasonV1 | null;
    hasGivenPermission?: YesNoUnknownV1 | null;
    practitioner?: string | null;
    practitionerPhone?: string | null;
    isInICU?: YesNoUnknownV1 | null;
    admittedInICUAt?: Date | null;
}

export type HospitalCommonDTO = DTO<HospitalCommon>;

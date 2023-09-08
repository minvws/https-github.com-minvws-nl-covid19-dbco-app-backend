/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { IntakeContactV1 } from '@dbco/schema/intakeContact/intakeContactV1';
import { IntakeFragmentV1 } from '@dbco/schema/intakeFragment/intakeFragmentV1';
import { GenderV1 } from '@dbco/enum';
import { IntakeTypeV1 } from '@dbco/enum';

/**
 * IntakeCommon
 */
export interface IntakeCommon {
    uuid: string;
    type: IntakeTypeV1;
    source: string;
    identifierType: string;
    identifier: string;
    pseudoBsnGuid: string;
    cat1Count?: number | null;
    estimatedCat2Count?: number | null;
    firstname?: string | null;
    prefix?: string | null;
    lastname?: string | null;
    dateOfBirth: Date;
    dateOfSymptomOnset?: Date | null;
    dateOfTest: Date;
    receivedAt: Date;
    createdAt: Date;
    fragments?: IntakeFragmentV1[] | null;
    contacts?: IntakeContactV1[] | null;
    pc3: string;
    gender: GenderV1;
}

export type IntakeCommonDTO = DTO<IntakeCommon>;

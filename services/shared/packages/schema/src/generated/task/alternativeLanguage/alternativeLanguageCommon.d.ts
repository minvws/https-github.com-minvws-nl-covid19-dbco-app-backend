/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { EmailLanguageV1 } from '@dbco/enum';
import { LanguageV1 } from '@dbco/enum';
import { YesNoUnknownV1 } from '@dbco/enum';

/**
 * AlternativeLanguageCommon
 */
export interface AlternativeLanguageCommon {
    useAlternativeLanguage?: YesNoUnknownV1 | null;
    phoneLanguages?: LanguageV1[] | null;
    emailLanguage?: EmailLanguageV1 | null;
}

export type AlternativeLanguageCommonDTO = DTO<AlternativeLanguageCommon>;

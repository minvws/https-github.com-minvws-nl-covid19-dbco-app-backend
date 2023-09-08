/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { EduDaycareV1 } from '@dbco/schema/covidCase/eduDaycare/eduDaycareV1';
import { ImmunityV1 } from '@dbco/schema/covidCase/immunity/immunityV1';
import { IndexV1 } from '@dbco/schema/covidCase/index/indexV1';
import { PregnancyV1 } from '@dbco/schema/covidCase/pregnancy/pregnancyV1';
import { RecentBirthV1 } from '@dbco/schema/covidCase/recentBirth/recentBirthV1';
import { RiskLocationV1 } from '@dbco/schema/covidCase/riskLocation/riskLocationV1';
import { SourceEnvironmentsV1 } from '@dbco/schema/covidCase/sourceEnvironments/sourceEnvironmentsV1';

/**
 * CovidCaseV1UpTo4
 */
export interface CovidCaseV1UpTo4 {
    index: IndexV1;
    eduDaycare: EduDaycareV1;
    immunity: ImmunityV1;
    pregnancy: PregnancyV1;
    recentBirth: RecentBirthV1;
    riskLocation: RiskLocationV1;
    sourceEnvironments: SourceEnvironmentsV1;
}

export type CovidCaseV1UpTo4DTO = DTO<CovidCaseV1UpTo4>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { CommunicationV3 } from '@dbco/schema/covidCase/communication/communicationV3';
import { EduDaycareV2 } from '@dbco/schema/covidCase/eduDaycare/eduDaycareV2';
import { ExtensiveContactTracingV3 } from '@dbco/schema/covidCase/extensiveContactTracing/extensiveContactTracingV3';
import { ImmunityV2 } from '@dbco/schema/covidCase/immunity/immunityV2';
import { IndexV2 } from '@dbco/schema/covidCase/index/indexV2';
import { PregnancyV2 } from '@dbco/schema/covidCase/pregnancy/pregnancyV2';
import { RecentBirthV2 } from '@dbco/schema/covidCase/recentBirth/recentBirthV2';
import { RiskLocationV2 } from '@dbco/schema/covidCase/riskLocation/riskLocationV2';
import { SourceEnvironmentsV2 } from '@dbco/schema/covidCase/sourceEnvironments/sourceEnvironmentsV2';

/**
 * CovidCaseV5Up
 */
export interface CovidCaseV5Up {
    index: IndexV2;
    communication: CommunicationV3;
    eduDaycare: EduDaycareV2;
    extensiveContactTracing: ExtensiveContactTracingV3;
    immunity: ImmunityV2;
    pregnancy: PregnancyV2;
    recentBirth: RecentBirthV2;
    riskLocation: RiskLocationV2;
    sourceEnvironments: SourceEnvironmentsV2;
}

export type CovidCaseV5UpDTO = DTO<CovidCaseV5Up>;

/**
 * *** WARNING ***
 * This code is auto-generated. Any changes will be reverted by generating the schema!
 */

import { DTO } from '@dbco/schema/dto';
import { AbroadV1 } from '@dbco/schema/covidCase/abroad/abroadV1';
import { AlternateContactV1 } from '@dbco/schema/covidCase/alternateContact/alternateContactV1';
import { AlternateResidencyV1 } from '@dbco/schema/covidCase/alternateResidency/alternateResidencyV1';
import { AlternativeLanguageV1 } from '@dbco/schema/covidCase/alternativeLanguage/alternativeLanguageV1';
import { CaseListV1 } from '@dbco/schema/caseList/caseListV1';
import { ContactV1 } from '@dbco/schema/covidCase/contact/contactV1';
import { DeceasedV1 } from '@dbco/schema/covidCase/deceased/deceasedV1';
import { GeneralPractitionerV1 } from '@dbco/schema/covidCase/generalPractitioner/generalPractitionerV1';
import { GroupTransportV1 } from '@dbco/schema/covidCase/groupTransport/groupTransportV1';
import { HospitalV1 } from '@dbco/schema/covidCase/hospital/hospitalV1';
import { HousematesV1 } from '@dbco/schema/covidCase/housemates/housematesV1';
import { JobV1 } from '@dbco/schema/covidCase/job/jobV1';
import { MedicationV1 } from '@dbco/schema/covidCase/medication/medicationV1';
import { OrganisationV1 } from '@dbco/schema/organisation/organisationV1';
import { PrincipalContextualSettingsV1 } from '@dbco/schema/covidCase/principalContextualSettings/principalContextualSettingsV1';
import { UserV1 } from '@dbco/schema/user/userV1';
import { AutomaticAddressVerificationStatusV1 } from '@dbco/enum';
import { BcoPhaseV1 } from '@dbco/enum';
import { BcoStatusV1 } from '@dbco/enum';
import { IndexStatusV1 } from '@dbco/enum';

/**
 * CovidCaseCommon
 */
export interface CovidCaseCommon {
    uuid: string;
    caseId?: string | null;
    hpzoneNumber?: string | null;
    organisation: OrganisationV1;
    assignedOrganisation?: OrganisationV1 | null;
    assignedCaseList?: CaseListV1 | null;
    assignedUser?: UserV1 | null;
    bcoStatus: BcoStatusV1;
    bcoPhase?: BcoPhaseV1 | null;
    indexStatus?: IndexStatusV1 | null;
    createdAt: Date;
    updatedAt: Date;
    completedAt?: Date | null;
    deletedAt?: Date | null;
    automaticAddressVerificationStatus: AutomaticAddressVerificationStatusV1;
    abroad: AbroadV1;
    alternateContact: AlternateContactV1;
    contact: ContactV1;
    deceased: DeceasedV1;
    job: JobV1;
    hospital: HospitalV1;
    housemates: HousematesV1;
    alternativeLanguage: AlternativeLanguageV1;
    alternateResidency: AlternateResidencyV1;
    generalPractitioner: GeneralPractitionerV1;
    principalContextualSettings: PrincipalContextualSettingsV1;
    groupTransport: GroupTransportV1;
    medication: MedicationV1;
    osirisNumber?: number | null;
}

export type CovidCaseCommonDTO = DTO<CovidCaseCommon>;

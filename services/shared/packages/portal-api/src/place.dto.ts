import type { Address } from './address';
import type { PaginatedRequestOptions } from './pagination';
import type { ContextRelationshipV1 } from '@dbco/enum';
import type { CircumstancesCommonDTO } from '@dbco/schema/context/circumstances/circumstancesCommon';
import type { DeceasedCommonDTO } from '@dbco/schema/covidCase/deceased/deceasedCommon';
import type { HospitalCommonDTO } from '@dbco/schema/covidCase/hospital/hospitalCommon';
import type { SymptomsCommonDTO } from '@dbco/schema/covidCase/symptoms/symptomsCommon';
import type { CalendarDateRange } from './case.dto';

export interface PlaceSituation {
    uuid: string;
    name: string;
    value: string;
}

export interface PlaceDTO {
    uuid: string;
    label: string | null;
    category: string;
    categoryLabel: string | null;
    indexCountSinceReset: number | null;
    indexCountResetAt: string | null;
    address: Address | null;
    addressLabel: string | null;
    indexCount: number;
    isVerified: boolean;
    organisationUuidByPostalCode: string | null;
    organisationUuid: string | null;
    source: 'manual' | 'external';
    ggd: {
        code: string | null;
        municipality: string | null;
    };
    createdAt: string;
    updatedAt: string;
    lastIndexPresence: string | null;
    situationNumbers: PlaceSituation[];
    sections: Array<string> | null;
}
export type Place = {
    uuid: string;
    label: string;
    category: string;
    address: Address;
    street: string;
    housenumber: string;
    housenumberSuffix?: string;
    postalcode: string;
    town: string;
    country: string;
    indexCount: number;
    createdAt: Date;
    updatedAt: Date;
    editable: boolean;
    isVerified: boolean;
    source: 'manual' | 'external';
};

export interface LocationDTO {
    id: string;
    label: string;
    indexCount: number;
    isVerified: boolean;
    category: string;
    addressLabel: string;
    address: Address | null;
    ggd: {
        code: string | null;
        municipality: string | null;
    };
}

export interface PlaceListResponse {
    from: number | null;
    to: number | null;
    total: number;
    currentPage: number;
    lastPage: number;
    data: PlaceDTO[];
}

export interface PlaceCasesTable extends PaginatedRequestOptions<PlaceCasesSortOptions> {
    infiniteId: number;
    fetchedPages: number[];
}

export enum PlaceSortOptions {
    INDEX_COUNT = 'indexCount',
    LAST_INDEX_PRESENCE = 'lastIndexPresence',
    INDEX_COUNT_SINCE_RESET = 'indexCountSinceReset',
}

export interface PlaceTable extends PaginatedRequestOptions<PlaceSortOptions> {
    infiniteId: number;
}

export enum PlaceCasesSortOptions {
    CREATED_AT = 'createdAt',
    EXPIRES_AT = 'expiresAt',
    STATUS = 'status',
}

export interface PlaceCasesResponse {
    canChangeOrganisation: boolean;
    caseId: string;
    causeForConcern: CircumstancesCommonDTO['causeForConcern'];
    createdAt: string;
    dateOfBirth: string;
    dateOfSymptomOnset: string | null;
    dateOfTest: string | null;
    firstName: string | null;
    hospital: HospitalCommonDTO;
    isDeceased: DeceasedCommonDTO['isDeceased'];
    lastName: string | null;
    moments: CalendarDateRange[];
    mostRecentVaccinationDate: string | null;
    name: string | null;
    notificationNamedConsent: boolean | null;
    relationContext: ContextRelationshipV1;
    sections: Array<string> | null;
    symptoms: SymptomsCommonDTO;
    token: string;
    uuid: string;
    vaccinationCount: string | number | null;
}

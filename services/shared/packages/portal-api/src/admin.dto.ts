import type {
    CalendarItemConfigStrategyIdentifierTypeV1,
    CalendarItemV1,
    CalendarPeriodColorV1,
    CalendarPointColorV1,
    ContactOriginDateV1,
    ContactRiskProfileV1,
    DateOperationIdentifierV1,
    IndexOriginDateV1,
    IndexRiskProfileV1,
    PeriodCalendarStrategyTypeV1,
    PointCalendarStrategyTypeV1,
    PolicyPersonTypeV1,
    PolicyVersionStatusV1,
    DateOperationRelativeDayV1,
} from '@dbco/enum';

export interface CalendarItem {
    uuid: string;
    personType: PolicyPersonTypeV1;
    color: CalendarPeriodColorV1 | CalendarPointColorV1;
    isDeletable: boolean;
    itemType: CalendarItemV1;
    label: string;
    policyVersionUuid: string;
    fixedCalendarName?: string;
}
export interface CalendarItemConfig {
    uuid: string;
    isHidden: boolean;
    isHideable: boolean;
    itemType: CalendarItemV1;
    label: string;
    strategies: CalendarItemConfigStrategy[];
}
export interface CalendarItemConfigDateOperation {
    uuid: string;
    identifierType: DateOperationIdentifierV1;
    originDateType: ContactOriginDateV1 | IndexOriginDateV1;
    relativeDay: DateOperationRelativeDayV1 | number;
}
export interface CalendarItemConfigStrategy {
    uuid: string;
    strategyType: PeriodCalendarStrategyTypeV1 | PointCalendarStrategyTypeV1;
    identifierType: CalendarItemConfigStrategyIdentifierTypeV1;
    dateOperations: CalendarItemConfigDateOperation[];
}

export interface CalendarView {
    uuid: string;
    label: string;
    policyVersionStatus: PolicyVersionStatusV1;
    policyVersionUuid: string;
    calendarItems: CalendarItem[];
    calendarViewEnum: string;
}

export interface PolicyGuideline {
    uuid: string;
    name: string;
    identifier: string;
    policyVersionUuid: string;
    policyVersionStatus: PolicyVersionStatusV1;
    sourceStartDateReference?: string;
    sourceStartDateAddition?: number;
    sourceEndDateReference?: string;
    sourceEndDateAddition?: number;
    contagiousStartDateReference?: string;
    contagiousStartDateAddition?: number;
    contagiousEndDateReference?: string;
    contagiousEndDateAddition?: number;
}

export interface PolicyVersion {
    uuid: string;
    name: string;
    startDate: Date | string;
    status: PolicyVersionStatusV1;
}

export interface RiskProfile {
    uuid: string;
    name: string;
    personTypeEnum: PolicyPersonTypeV1;
    policyGuidelineUuid: string;
    policyVersionUuid: string;
    riskProfileEnum: ContactRiskProfileV1 | IndexRiskProfileV1;
    sortOrder: number;
    isActive: boolean;
}

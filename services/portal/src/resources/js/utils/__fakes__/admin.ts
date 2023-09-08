import { fakerjs } from '@/utils/test';
import {
    CalendarItemConfigStrategyIdentifierTypeV1,
    CalendarItemV1,
    CalendarPeriodColorV1,
    IndexRiskProfileV1,
    indexRiskProfileV1Options,
    PointCalendarStrategyTypeV1,
    PolicyPersonTypeV1,
    PolicyVersionStatusV1,
} from '@dbco/enum';
import type {
    CalendarItem,
    CalendarItemConfig,
    CalendarItemConfigStrategy,
    CalendarView,
    PolicyGuideline,
    PolicyVersion,
    RiskProfile,
} from '@dbco/portal-api/admin.dto';
import { createFakeDataGenerator } from './createFakeDataGenerator';

export const fakeCalendarItemConfigStrategy = createFakeDataGenerator<CalendarItemConfigStrategy>(() => ({
    uuid: fakerjs.string.uuid(),
    identifierType: CalendarItemConfigStrategyIdentifierTypeV1.VALUE_point,
    strategyType: PointCalendarStrategyTypeV1.VALUE_pointFixedStrategy,
    dateOperations: [],
}));

export const fakeCalendarItemConfig = createFakeDataGenerator<CalendarItemConfig>(() => ({
    uuid: fakerjs.string.uuid(),
    isHideable: false,
    isHidden: false,
    itemType: CalendarItemV1.VALUE_point,
    label: fakerjs.lorem.word(),
    strategies: [fakeCalendarItemConfigStrategy() as CalendarItemConfigStrategy],
}));

export const fakeCalendarItem = createFakeDataGenerator<CalendarItem>(() => ({
    uuid: fakerjs.string.uuid(),
    personType: PolicyPersonTypeV1.VALUE_index,
    color: CalendarPeriodColorV1.VALUE_light_pink,
    isDeletable: false,
    itemType: CalendarItemV1.VALUE_period,
    label: fakerjs.lorem.word(),
    policyVersionUuid: fakerjs.string.uuid(),
}));

export const fakeCalendarView = createFakeDataGenerator<CalendarView>(() => ({
    uuid: fakerjs.string.uuid(),
    calendarItems: [fakeCalendarItem()],
    calendarViewEnum: 'index_siderbar',
    label: fakerjs.lorem.word(),
    policyVersionStatus: PolicyVersionStatusV1.VALUE_draft,
    policyVersionUuid: fakerjs.string.uuid(),
}));

export const fakePolicyGuideline = createFakeDataGenerator<PolicyGuideline>(() => ({
    uuid: fakerjs.string.uuid(),
    name: 'Symptomatisch - Standaard',
    identifier: fakerjs.lorem.word(),
    policyVersionUuid: fakerjs.string.uuid(),
    policyVersionStatus: PolicyVersionStatusV1.VALUE_draft,
}));

export const fakePolicyVersion = createFakeDataGenerator<PolicyVersion>(() => ({
    uuid: fakerjs.string.uuid(),
    name: 'OMT advies mm-dd-jjjj',
    startDate: fakerjs.date.past(),
    status: PolicyVersionStatusV1.VALUE_draft,
}));

export const fakeRiskProfile = createFakeDataGenerator<RiskProfile>(() => ({
    uuid: fakerjs.string.uuid(),
    name: indexRiskProfileV1Options['is_immuno_compromised'],
    personTypeEnum: PolicyPersonTypeV1.VALUE_index,
    policyVersionUuid: fakerjs.string.uuid(),
    policyGuidelineUuid: fakerjs.string.uuid(),
    riskProfileEnum: IndexRiskProfileV1.VALUE_is_immuno_compromised,
    sortOrder: fakerjs.number.int(),
    isActive: fakerjs.datatype.boolean(),
}));

import { CalendarPeriodColorV1 } from '@dbco/enum';
import type { CalendarDateRange } from '@dbco/portal-api/case.dto';
import { createFakeDataGenerator } from './createFakeDataGenerator';
import { fakerjs } from '../test';

export const fakeCalendarDateRange = createFakeDataGenerator<CalendarDateRange>(() => ({
    id: fakerjs.lorem.word(),
    type: 'period',
    endDate: fakerjs.date.soon(),
    startDate: fakerjs.date.recent(),
    label: fakerjs.lorem.word(),
    color: CalendarPeriodColorV1.VALUE_light_blue,
    icon: 'verified',
}));

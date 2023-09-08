import { formatDate } from '@/utils/date';
import { fakerjs } from '@/utils/test';
import type { Context } from '@dbco/portal-api/context.dto';
import { createFakeDataGenerator } from './createFakeDataGenerator';

export const fakeContext = createFakeDataGenerator<Context>(() => ({
    uuid: fakerjs.string.uuid(),
    label: fakerjs.lorem.words(2),
    remarks: fakerjs.lorem.sentence(),
    explanation: fakerjs.lorem.sentence(),
    detailedExplanation: fakerjs.lorem.paragraph(),
    moments: fakerjs.date
        .betweens({ from: fakerjs.date.past(), to: new Date(), count: 3 })
        .map((date) => formatDate(date, 'yyyy-MM-dd')),
    placeUuid: fakerjs.string.uuid(),
}));

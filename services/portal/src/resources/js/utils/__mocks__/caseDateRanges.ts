import { faker } from '@faker-js/faker';
import type { Range } from '@/utils/caseDateRanges';
import * as caseDateRanges from '@/utils/caseDateRanges';

type fakeDateRange = {
    end: Date;
    start: Date;
};

export const fakeRangeSize = 5;

const mockCaseDateRanges = (infectiousDates?: fakeDateRange, sourceDates?: fakeDateRange) => {
    return vi.spyOn(caseDateRanges, 'caseDateRanges').mockImplementationOnce(
        () =>
            <Range[]>[
                {
                    endDate: infectiousDates?.end || faker.date.soon(),
                    key: 'infectious',
                    startDate: infectiousDates?.start || faker.date.recent(),
                },
                {
                    endDate: sourceDates?.end || faker.date.soon(),
                    key: 'source',
                    startDate: sourceDates?.start || faker.date.recent(),
                },
            ]
    );
};

export default mockCaseDateRanges;

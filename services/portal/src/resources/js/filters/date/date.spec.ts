import {
    dateFnsFormat,
    dateFormat,
    dateFormatDeltaTime,
    dateFormatMonth,
    dateFormatLong,
    dateTimeFormatLong,
    dateLast3Days,
    dateFormatMonthLong,
} from './date';

import { format, subDays } from 'date-fns';

import { formatDate as dateUtilsFormat } from '@/utils/date';

beforeAll(() => {
    vi.useFakeTimers();
});

afterAll(() => {
    vi.useRealTimers();
});

describe('dateFnsFormat', () => {
    it('should pass the values onto the date utils', () => {
        expect(dateFnsFormat('2021-01-01T14:15:00', 'yyyy-MM-dd HH:mm')).toEqual('2021-01-01 14:15');
    });

    it('format is not required and has a default', () => {
        expect(dateFnsFormat('2021-01-01')).toEqual('01-01-2021');
    });

    it.each([[''], [null], [undefined]])('should return an empty string if the input is %s', (value) => {
        expect(dateFnsFormat(value)).toEqual('');
    });
});

describe('dateFormatMonth', () => {
    it('should format the date', () => {
        expect(dateFormatMonth('2021-01-01T14:15:00')).toEqual('1 jan. 2021');
    });
});

describe('dateFormatMonthLong', () => {
    it('should format the date', () => {
        expect(dateFormatMonthLong('2021-01-01T14:15:00')).toEqual('1 januari 2021');
    });
});

describe('dateFormat', () => {
    it('should format the date', () => {
        expect(dateFormat('2021-01-01T14:15:00')).toEqual('01-01-2021');
    });
});

describe('dateFormatLong', () => {
    it('should format the date', () => {
        expect(dateFormatLong('2021-01-01T14:15:00')).toEqual('vrijdag 1 jan.');
    });
});

describe('dateTimeFormatLong', () => {
    it('should format the date', () => {
        expect(dateTimeFormatLong('2021-01-01T14:15:00')).toEqual('01 jan. 2021 om 14:15');
    });
});

describe('dateFormatDeltaTime', () => {
    it.each([[''], [null], [undefined]])('should return an empty string if the input is %s', (value) => {
        expect(dateFormatDeltaTime(value)).toEqual('');
    });
    it.each([
        [format(Date.now(), 'dd-MM-yyyy'), 'dd-MM-yyyy', 'Vandaag '],
        [format(subDays(Date.now(), 1), 'dd-MM-yyyy'), 'dd-MM-yyyy', 'Gisteren '],
        [
            format(subDays(Date.now(), 2), 'dd-MM-yyyy'),
            'dd-MM-yyyy',
            dateUtilsFormat(subDays(Date.now(), 2), 'EEEE') + ' ',
        ],
        [
            format(subDays(Date.now(), 7), 'dd-MM-yyyy'),
            'dd-MM-yyyy',
            dateUtilsFormat(subDays(Date.now(), 7), 'EEEE d MMM') + ' ',
        ],
    ])('should format the date %s', (value, format, expected) => {
        expect(dateFormatDeltaTime(value, format)).toEqual(expected);
    });
});

describe('dateLast3Days', () => {
    it.each([[''], [null], [undefined]])('should return an empty string if the input is %s', (value) => {
        expect(dateLast3Days(value)).toEqual('');
    });
    it.each([
        [subDays(Date.now(), -2), ''],
        [subDays(Date.now(), -1), ''],
        [Date.now(), 'Vandaag'],
        [subDays(Date.now(), 1), 'Gisteren'],
        [subDays(Date.now(), 2), 'Eergisteren'],
        [subDays(Date.now(), 3), ''],
    ])('should format the date %s', (value, expected) => {
        expect(dateLast3Days(value)).toEqual(expected);
    });
});

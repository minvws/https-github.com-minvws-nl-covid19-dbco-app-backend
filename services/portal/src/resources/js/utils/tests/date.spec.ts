import { add, sub } from 'date-fns';
import * as date from '../date';
import { getMonday } from '../date';

describe('ensureDate', () => {
    it.each([
        ['2021-01-01', new Date('2021-01-01')],
        [new Date('2021-01-01'), new Date('2021-01-01')],
        ['', new Date(NaN)],
        ['not a date', new Date(NaN)],
    ])('should return %s as a date', (givenDate, expectation) => {
        expect(date.ensureDate(givenDate)).toStrictEqual(expectation);
    });
});

describe('includesDay', () => {
    it.each([
        [true, '2021-01-01', ['2021-01-01']],
        [true, '2021-01-01', [new Date('2021-01-01')]],
        [true, new Date('2021-01-01'), ['2021-01-01']],
        [true, '2021-01-01', ['2021-01-01', '2021-01-02']],
        [true, new Date('2021-01-01'), [new Date('2021-01-01'), new Date('2021-01-02')]],
        [true, '2021-01-01', ['2021-01-01', '2021-01-02', '2021-01-03']],
        [true, '2021-01-01', [new Date('2021-01-01'), '2021-01-02', new Date('2021-01-03')]],
        [true, new Date('2021-01-01'), [new Date('2021-01-01'), '2021-01-02', '2021-01-03']],
        [false, '2021-01-01', []],
        [false, '2021-01-01', ['2021-01-02', '2021-01-03']],
        [false, '', ['2021-01-01']],
        [false, 'not a date', ['2021-01-01']],
    ])('should return %s if date %s is in the array %s', (expectation, givenDate, array) => {
        expect(date.includesDay(array, givenDate)).toStrictEqual(expectation);
    });
});

describe('parseDate', () => {
    beforeAll(() => {
        vi.useFakeTimers();
    });

    afterAll(() => {
        vi.useRealTimers();
    });

    it.each([
        ['', '', new Date('2021-01-01 00:00')],
        ['2021-01-01', 'yyyy-MM-dd', new Date(2021, 0, 1)],
        ['01-01-2021', 'yyyy-MM-dd', new Date(2021, 0, 1)],
        ['20-08-2021', 'dd-MM-yyyy', new Date(2021, 7, 20)],
    ])('should parse %s with format %s to %s', (dateString, format, expectation) => {
        vi.setSystemTime(new Date('2021-01-01 00:00'));
        expect(date.parseDate(dateString, format)).toStrictEqual(expectation);
    });

    it.each([
        ['2021-01-01T00:00:00.000000Z', new Date(2021, 0, 1)],
        ['2021-01-01T12:34:56.000000Z', new Date(2021, 0, 1, 12, 34, 56)],
    ])('should parse %s to date %s', (dateString, expectation) => {
        expect(date.parseDate(dateString)).toStrictEqual(expectation);
    });

    it.each([['01-01-2021', 'yyyy-MM-dd', new Date(2021, 2, 1)]])(
        'should not parse %s with format %s to date %s',
        (dateString, format, expectation) => {
            vi.setSystemTime(new Date('2021-03-01 00:00'));
            expect(date.parseDate(dateString, format)).toStrictEqual(expectation);
        }
    );
});

describe('formatDate', () => {
    it.each([
        [new Date(2021, 0, 1), 'dd-MM-yyyy', '01-01-2021'],
        [new Date(2021, 0, 4), 'yyyy-dd', '2021-04'],
        [new Date(2021, 2, 4), 'yyyy-MM-dd', '2021-03-04'],
        [new Date(2021, 0, 1, 14, 15), 'yyyy-MM-dd HH:mm', '2021-01-01 14:15'],
    ])('should format %s with format %s to %s', (dateObject, format, expectation) => {
        expect(date.formatDate(dateObject, format)).toStrictEqual(expectation);
    });
});

describe('formatInOtherTimeZone', () => {
    it.each([
        ['2022-03-22T22:59:59Z', 'Europe/Lisbon', 'dd-MM-yyyy', '22-03-2022'],
        ['2022-04-01T22:59:59Z', 'Europe/Lisbon', 'dd-MM-yyyy', '01-04-2022'],
        ['2022-11-08T22:59:59Z', 'Europe/Lisbon', 'dd-MM-yyyy', '08-11-2022'],
        ['2022-10-26T22:59:59Z', 'Europe/Lisbon', 'dd-MM-yyyy', '26-10-2022'],
    ])('should format %s to date in timeZone %s with format %s to %s', (dateObject, timeZone, format, expectation) => {
        expect(date.formatInOtherTimeZone(dateObject, timeZone, format)).toStrictEqual(expectation);
    });
});

describe('getDifferenceInDays', () => {
    it.each([
        [new Date(2021, 0, 1), new Date(2021, 0, 1), 0],
        [new Date(2021, 0, 4), new Date(2021, 0, 2), 2],
        [new Date(2021, 10, 15), new Date(2021, 2, 4), 256],
        [new Date(2021, 2, 4, 14, 15), new Date(2021, 1, 3, 13, 15), 29],
    ])('should find difference in days between %s and %s equals %s', (dateLeft, dateRight, expectation) => {
        expect(date.getDifferenceInDays(dateLeft, dateRight)).toBe(expectation);
    });
});

describe('isBetweenDays', () => {
    it.each([
        [true, new Date(2021, 0, 10), new Date(2021, 0, 5), new Date(2021, 0, 15), '()'],
        [false, new Date(2021, 0, 20), new Date(2021, 0, 5), new Date(2021, 0, 15), '()'],
        [false, new Date(2021, 0, 5), new Date(2021, 0, 5), new Date(2021, 0, 15), '()'],
        [true, new Date(2021, 0, 5), new Date(2021, 0, 5), new Date(2021, 0, 15), '[]'],
        [true, new Date(2021, 0, 5, 13), new Date(2021, 0, 5, 15), new Date(2021, 0, 15, 20), '[]'],
        [false, new Date(2021, 0, 5, 13), new Date(2021, 0, 5, 15), new Date(2021, 0, 15, 20), '()'],
    ])('should return %s', (expectation, checkDate, from, to, inclusivity) => {
        expect(date.isBetweenDays(checkDate, from, to, inclusivity)).toBe(expectation);
    });
});

describe('getMonday', () => {
    it('should return the monday of the date provided when the date provided is a sunday', () => {
        expect(getMonday(new Date('2021-05-31T00:00:00.000Z'))).toStrictEqual(new Date('2021-05-31T00:00:00.000Z'));
    });

    it('should return the monday of the date provided when the date provided is a monday', () => {
        expect(getMonday(new Date('2021-06-07T00:00:00.000Z'))).toStrictEqual(new Date('2021-06-07T00:00:00.000Z'));
    });

    it('should return the monday of the date provided when the date provided is a tuesday', () => {
        expect(getMonday(new Date('2021-06-08T00:00:00.000Z'))).toStrictEqual(new Date('2021-06-07T00:00:00.000Z'));
    });
});

describe('formatDateLong', () => {
    it('should return the date, written in Dutch, with a full weekday format (d MMM EEEE) ', () => {
        const someDateInJune = new Date('2021-06-30T00:00:00');
        expect(date.formatDateLong(someDateInJune)).toBe('woensdag 30 jun.');
    });
});

describe('formatTime', () => {
    it('should return the same value if falsy/null/NaN', () => {
        expect(date.formatTime(null)).toBe(null);
        expect(date.formatTime('')).toBe('');
        expect(date.formatTime('aaaa')).toBe('aaaa');
    });

    it('should return the right format', () => {
        expect(date.formatTime('0')).toBe('00:00');
        expect(date.formatTime('1')).toBe('01:00');
        expect(date.formatTime('20')).toBe('20:00');
        expect(date.formatTime('123')).toBe('01:23');
        expect(date.formatTime('901')).toBe('09:01');
        expect(date.formatTime('2315')).toBe('23:15');

        expect(date.formatTime('0.0')).toBe('00:00');
        expect(date.formatTime('5.0')).toBe('05:00');
        expect(date.formatTime('0.5')).toBe('00:05');
        expect(date.formatTime('0.12')).toBe('00:12');
        expect(date.formatTime('14.0')).toBe('14:00');
        expect(date.formatTime('7.00')).toBe('07:00');
        expect(date.formatTime('12.34')).toBe('12:34');

        expect(date.formatTime('0:0')).toBe('00:00');
        expect(date.formatTime('2:0')).toBe('02:00');
        expect(date.formatTime('0:3')).toBe('00:03');
        expect(date.formatTime('0:10')).toBe('00:10');
        expect(date.formatTime('10:0')).toBe('10:00');
        expect(date.formatTime('7:00')).toBe('07:00');
        expect(date.formatTime('12:34')).toBe('12:34');
    });
});

describe('getDifferenceStringForTwoDates', () => {
    it.each([
        ['', null, null, ''],
        ['gelijk aan dateTwoLabel', new Date(), new Date(), 'dateTwoLabel'],
        ['1 week voor dateTwoLabel', new Date(), add(new Date(), { days: 7 }), 'dateTwoLabel'],
        ['1 dag voor dateTwoLabel', new Date(), add(new Date(), { days: 1 }), 'dateTwoLabel'],
        ['1 week na dateTwoLabel', new Date(), add(new Date(), { days: -7 }), 'dateTwoLabel'],
        ['1 dag na dateTwoLabel', new Date(), add(new Date(), { days: -1 }), 'dateTwoLabel'],
        ['2 weken voor dateTwoLabel', new Date(), add(new Date(), { days: 14 }), 'dateTwoLabel'],
        ['2 dagen voor dateTwoLabel', new Date(), add(new Date(), { days: 2 }), 'dateTwoLabel'],
        ['2 weken na dateTwoLabel', new Date(), add(new Date(), { days: -14 }), 'dateTwoLabel'],
        ['2 dagen na dateTwoLabel', new Date(), add(new Date(), { days: -2 }), 'dateTwoLabel'],
        ['1 week en 2 dagen voor dateTwoLabel', new Date(), add(new Date(), { days: 9 }), 'dateTwoLabel'],
        ['2 weken en 1 dag na dateTwoLabel', new Date(), add(new Date(), { days: -15 }), 'dateTwoLabel'],
        ['3 weken en 2 dagen geleden', new Date(), add(new Date(), { days: -23 }), null],
        ['3 dagen geleden', add(new Date(), { days: 3 }), new Date(), null],
        ['vandaag', new Date(), new Date(), null],
    ])('should return %s', (expectation, dateOne, dateTwo, dateTwoLabel) => {
        expect(date.getDifferenceStringForTwoDates(dateOne, dateTwo, dateTwoLabel)).toBe(expectation);
    });
});

describe('Localized strings', () => {
    beforeAll(() => {
        vi.useFakeTimers();
    });

    afterAll(() => {
        vi.useRealTimers();
    });

    // Test multiple dates to prevent issues based on current date
    const testDates = [
        // Basic, now
        new Date(),
        // Leap day (29th of february)
        new Date(2020, 1, 29),
        // Test end of a long month to filter out setMonth bugs (31st of July)
        new Date(2022, 6, 31),
        // Timezone bugs
        new Date(2022, 6, 31, 23, 59, 59),
    ];

    describe('getDayNames', () => {
        it.each(testDates)('should return the days of the week in dutch (testing with date=%s)', (testDate) => {
            vi.setSystemTime(testDate);

            expect(date.getDayNames()).toStrictEqual([
                'zondag',
                'maandag',
                'dinsdag',
                'woensdag',
                'donderdag',
                'vrijdag',
                'zaterdag',
            ]);
        });
    });

    describe('getMonthNames', () => {
        it.each(testDates)('should return the months of the year in dutch (testing with date=%s)', (testDate) => {
            vi.setSystemTime(testDate);

            expect(date.getMonthNames()).toStrictEqual([
                'januari',
                'februari',
                'maart',
                'april',
                'mei',
                'juni',
                'juli',
                'augustus',
                'september',
                'oktober',
                'november',
                'december',
            ]);
        });
    });

    describe('getMonthNamesShort', () => {
        it.each(testDates)(
            'should return the short version of the months of the year in dutch (testing with date=%s)',
            (testDate) => {
                vi.setSystemTime(testDate);

                expect(date.getMonthNamesShort()).toStrictEqual([
                    'jan.',
                    'feb.',
                    'mrt.',
                    'apr.',
                    'mei',
                    'jun.',
                    'jul.',
                    'aug.',
                    'sep.',
                    'okt.',
                    'nov.',
                    'dec.',
                ]);
            }
        );
    });
});

describe('isAdjacentDate', () => {
    it.each([
        // same day
        [false, new Date(), new Date()],
        // adjacent days
        [true, new Date(), add(new Date(), { days: 1 })],
        [true, new Date(), sub(new Date(), { days: 1 })],
        [true, add(new Date(), { days: 1 }), new Date()],
        [true, sub(new Date(), { days: 1 }), new Date()],
        // none adjacent days
        [false, new Date(), add(new Date(), { weeks: 1 })],
        [false, new Date(), sub(new Date(), { weeks: 1 })],
        [false, add(new Date(), { weeks: 1 }), new Date()],
        [false, sub(new Date(), { weeks: 1 }), new Date()],
        [false, new Date(), add(new Date(), { years: 1 })],
        [false, new Date(), sub(new Date(), { years: 1 })],
        [false, add(new Date(), { years: 1 }), new Date()],
        [false, sub(new Date(), { years: 1 }), new Date()],
    ])('%#: should return %s for %s & %s', (expectation, dateOne, dateTwo) => {
        expect(date.isAdjacentDate(dateOne, dateTwo)).toBe(expectation);
    });
});

describe('getSummaryTextForDates', () => {
    it.each([
        ['', ['not a date']],
        ['1 jan.', ['01-01-2022']],
        ['1 jan.', ['01-01-2022', 'not a date']],
        ['1 - 2 jan.', ['01-01-2022', '01-02-2022']],
        ['1 - 2 jan.', ['01-01-2022', 'not a date', '01-02-2022']],
        ['1 - 3 jan.', ['01-01-2022', '01-02-2022', '01-03-2022']],
        ['1 - 3 jan.', ['01-01-2022', '01-02-2022', 'not a date', '01-03-2022']],
        ['1 jan., 1 feb.', ['01-01-2022', '02-01-2022']],
        ['1 jan., 1 feb.', ['01-01-2022', 'not a date', '02-01-2022']],
        ['1 - 3 jan., 1 feb.', ['01-01-2022', '01-02-2022', '01-03-2022', '02-01-2022']],
        ['1 - 3 jan., 1 - 2 feb.', ['01-01-2022', '01-02-2022', '01-03-2022', '02-01-2022', '02-02-2022']],
        ['11 jan.', ['01-11-2022']],
        ['11 - 12 jan.', ['01-11-2022', '01-12-2022']],
        ['11 - 13 jan.', ['01-11-2022', '01-12-2022', '01-13-2022']],
        ['11 jan., 11 feb.', ['01-11-2022', '02-11-2022']],
        ['11 - 13 jan., 11 feb.', ['01-11-2022', '01-12-2022', '01-13-2022', '02-11-2022']],
        ['11 - 13 jan., 11 - 12 feb.', ['01-11-2022', '01-12-2022', '01-13-2022', '02-11-2022', '02-12-2022']],
        ['21 jan.', ['01-21-2022']],
        ['21 - 22 jan.', ['01-21-2022', '01-22-2022']],
        ['21 - 23 jan.', ['01-21-2022', '01-22-2022', '01-23-2022']],
        ['21 jan., 21 feb.', ['01-21-2022', '02-21-2022']],
        ['21 - 23 jan., 21 feb.', ['01-21-2022', '01-22-2022', '01-23-2022', '02-21-2022']],
        ['21 - 23 jan., 21 - 22 feb.', ['01-21-2022', '01-22-2022', '01-23-2022', '02-21-2022', '02-22-2022']],
        ['1 jan.', [new Date('01-01-2022')]],
        ['1 - 2 jan.', [new Date('01-01-2022'), new Date('01-02-2022')]],
        ['1 - 2 jan.', [new Date('01-01-2022'), 'not a date', new Date('01-02-2022')]],
        ['1 - 3 jan.', ['01-01-2022', new Date('01-02-2022'), '01-03-2022']],
        ['1 - 3 jan.', ['01-01-2022', new Date('01-02-2022'), 'not a date', new Date('01-03-2022')]],
    ])('%#: should return "%s" for %j', (expectation, dates) => {
        expect(date.getSummaryTextForDates(dates)).toBe(expectation);
    });
});

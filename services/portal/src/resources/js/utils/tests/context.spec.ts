import { ContextGroup } from '@/components/form/ts/formTypes';
import { fakerjs } from '../test';
import {
    areAllDatesInOtherContextGroup,
    areDatesInContextGroup,
    classifyDates,
    getClassificationWarning,
    SourceAndContagiousDateClassification,
    formattedName,
} from '../context';
import type { Range } from '../caseDateRanges';

describe('classifyDates', () => {
    const expectationsWithoutOverlap = [
        // Edge: beginning of source period
        { dates: [new Date('2022-08-17')], expected: [SourceAndContagiousDateClassification.BEFORE_SOURCE] },
        { dates: [new Date('2022-08-18')], expected: [SourceAndContagiousDateClassification.SOURCE] },
        { dates: [new Date('2022-08-19')], expected: [SourceAndContagiousDateClassification.SOURCE] },

        // Edge: source+contagious period
        { dates: [new Date('2022-08-31')], expected: [SourceAndContagiousDateClassification.SOURCE] },
        { dates: [new Date('2022-09-01')], expected: [SourceAndContagiousDateClassification.CONTAGIOUS] },

        // Edge: end of contagious period
        { dates: [new Date('2022-09-05')], expected: [SourceAndContagiousDateClassification.CONTAGIOUS] },
        { dates: [new Date('2022-09-06')], expected: [SourceAndContagiousDateClassification.CONTAGIOUS] },
        { dates: [new Date('2022-09-07')], expected: [SourceAndContagiousDateClassification.AFTER_CONTAGIOUS] },

        // Multiple days: before beginning of source period
        {
            dates: [new Date('2022-08-01'), new Date('2022-08-02')],
            expected: [
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
            ],
        },

        // Multiple days: before beginning of source period, with one day in source period
        {
            dates: [new Date('2022-08-01'), new Date('2022-08-20')],
            expected: [
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
                SourceAndContagiousDateClassification.SOURCE,
            ],
        },

        // Multiple days after end of contagious period
        {
            dates: [new Date('2022-09-07'), new Date('2022-09-08')],
            expected: [
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
            ],
        },

        // Multiple days after end of contagious period, with one day in contagious period
        {
            dates: [new Date('2022-09-01'), new Date('2022-09-20')],
            expected: [
                SourceAndContagiousDateClassification.CONTAGIOUS,
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
            ],
        },

        // A lot of different dates
        {
            dates: [
                new Date('2022-09-01'),
                new Date('2022-08-01'),
                new Date('2022-10-05'),
                new Date('2022-08-20'),
                new Date('2022-09-03'),
            ],
            expected: [
                SourceAndContagiousDateClassification.CONTAGIOUS,
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
                SourceAndContagiousDateClassification.SOURCE,
                SourceAndContagiousDateClassification.CONTAGIOUS,
            ],
        },
    ];

    const sourcePeriod: Range = {
        startDate: new Date('2022-08-18'),
        endDate: new Date('2022-08-31'),
    };

    const contagiousPeriod: Range = {
        startDate: new Date('2022-09-01'),
        endDate: new Date('2022-09-06'),
    };

    it.concurrent.each(expectationsWithoutOverlap)(
        'no overlap: dates=$dates, expected=$expected',
        ({ dates, expected }) => {
            const classifications = classifyDates(dates, sourcePeriod, contagiousPeriod);

            expect(classifications).toEqual(expected);
        }
    );

    const expectationsWithOverlap = [
        // Edge: source+contagious period
        { dates: [new Date('2022-08-31')], expected: [SourceAndContagiousDateClassification.SOURCE] },
        { dates: [new Date('2022-09-01')], expected: [SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS] },
        { dates: [new Date('2022-09-02')], expected: [SourceAndContagiousDateClassification.CONTAGIOUS] },

        // A lot of different dates
        {
            dates: [
                new Date('2022-09-01'),
                new Date('2022-08-01'),
                new Date('2022-10-05'),
                new Date('2022-08-20'),
                new Date('2022-09-03'),
            ],
            expected: [
                SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS,
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
                SourceAndContagiousDateClassification.SOURCE,
                SourceAndContagiousDateClassification.CONTAGIOUS,
            ],
        },
    ];

    const sourcePeriodOverlap: Range = {
        startDate: new Date('2022-08-20'),
        endDate: new Date('2022-09-01'),
    };

    const contagiousPeriodOverlap: Range = {
        startDate: new Date('2022-09-01'),
        endDate: new Date('2022-09-13'),
    };

    it.concurrent.each(expectationsWithOverlap)('overlap: dates=$dates, expected=$expected', ({ dates, expected }) => {
        const classifications = classifyDates(dates, sourcePeriodOverlap, contagiousPeriodOverlap);

        expect(classifications).toEqual(expected);
    });
});

describe('getClassificationWarning', () => {
    const beforeSourcePeriodExpect = expect.stringContaining('vóór de bronperiode');
    const afterContagiousPeriodExpect = expect.stringContaining('na de besmettelijke periode');

    // Testset
    const expectations = [
        // No dates
        { classifications: [], expected: undefined },

        // Single dates
        { classifications: [SourceAndContagiousDateClassification.BEFORE_SOURCE], expected: beforeSourcePeriodExpect },
        { classifications: [SourceAndContagiousDateClassification.SOURCE], expected: undefined },
        { classifications: [SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS], expected: undefined },
        { classifications: [SourceAndContagiousDateClassification.CONTAGIOUS], expected: undefined },
        {
            classifications: [SourceAndContagiousDateClassification.AFTER_CONTAGIOUS],
            expected: afterContagiousPeriodExpect,
        },

        // Multiple dates - expecting warning
        {
            classifications: [
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
            ],
            expected: beforeSourcePeriodExpect,
        },
        {
            classifications: [
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
            ],
            expected: afterContagiousPeriodExpect,
        },

        // Multiple dates - expecting no warning
        {
            classifications: Object.values(SourceAndContagiousDateClassification),
            expected: undefined,
        },
        {
            classifications: [
                SourceAndContagiousDateClassification.BEFORE_SOURCE,
                SourceAndContagiousDateClassification.SOURCE,
            ],
            expected: undefined,
        },
        {
            classifications: [
                SourceAndContagiousDateClassification.CONTAGIOUS,
                SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
            ],
            expected: undefined,
        },
    ];

    it.concurrent.each(expectations)(
        'classifications=$classifications, expected=$expected',
        ({ classifications, expected }) => {
            const warning = getClassificationWarning(classifications);

            expect(warning).toStrictEqual(expected);
        }
    );
});

describe('areAllDatesInOtherContextGroup', () => {
    it.concurrent.each(Object.values(ContextGroup))('Should return false if 0 classifications, group=%s', (group) => {
        const result = areAllDatesInOtherContextGroup([], group);
        expect(result).toStrictEqual(false);
    });

    it(`should return false if group=${ContextGroup.All}`, () => {
        const result = areAllDatesInOtherContextGroup([SourceAndContagiousDateClassification.SOURCE], ContextGroup.All);
        expect(result).toStrictEqual(false);
    });

    it(`should return false if an overlapping date is included`, () => {
        const result = areAllDatesInOtherContextGroup(
            [SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS],
            ContextGroup.Source
        );
        expect(result).toStrictEqual(false);
    });

    describe(`Group=${ContextGroup.Source}`, () => {
        it('should return true if ALL dates are during or after the contagious period', () => {
            const result = areAllDatesInOtherContextGroup(
                [
                    SourceAndContagiousDateClassification.CONTAGIOUS,
                    SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
                ],
                ContextGroup.Source
            );
            expect(result).toStrictEqual(true);
        });

        it('should return false if NOT ALL dates are during or after the contagious period', () => {
            const result = areAllDatesInOtherContextGroup(
                [SourceAndContagiousDateClassification.SOURCE, SourceAndContagiousDateClassification.CONTAGIOUS],
                ContextGroup.Source
            );
            expect(result).toStrictEqual(false);
        });
    });

    describe(`Group=${ContextGroup.Contagious}`, () => {
        it('should return true if ALL dates are before or during the source period', () => {
            const result = areAllDatesInOtherContextGroup(
                [SourceAndContagiousDateClassification.BEFORE_SOURCE, SourceAndContagiousDateClassification.SOURCE],
                ContextGroup.Contagious
            );
            expect(result).toStrictEqual(true);
        });

        it('should return false if NOT ALL dates are before or during the source period', () => {
            const result = areAllDatesInOtherContextGroup(
                [SourceAndContagiousDateClassification.SOURCE, SourceAndContagiousDateClassification.CONTAGIOUS],
                ContextGroup.Contagious
            );
            expect(result).toStrictEqual(false);
        });
    });
});

describe('areDatesInContextGroup', () => {
    it.concurrent.each(Object.values(ContextGroup))('Should return false if 0 classifications, group=%s', (group) => {
        const result = areDatesInContextGroup([], group);
        expect(result).toStrictEqual(false);
    });

    it(`should return true if group=${ContextGroup.All}`, () => {
        const result = areDatesInContextGroup([SourceAndContagiousDateClassification.SOURCE], ContextGroup.All);
        expect(result).toStrictEqual(true);
    });

    it(`should return true if all classifications are unknown`, () => {
        const result = areDatesInContextGroup(
            [SourceAndContagiousDateClassification.UNKNOWN, SourceAndContagiousDateClassification.UNKNOWN],
            ContextGroup.Source
        );
        expect(result).toStrictEqual(true);
    });

    it(`should return false if group is unknown (no logic matched)`, () => {
        const result = areDatesInContextGroup(
            [SourceAndContagiousDateClassification.SOURCE],
            'unknown' as ContextGroup
        );
        expect(result).toStrictEqual(false);
    });

    it.concurrent.each(Object.values(ContextGroup))(
        'should return true if date is overlapping with both periods, group=%s',
        (group) => {
            const result = areDatesInContextGroup([SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS], group);
            expect(result).toStrictEqual(true);
        }
    );

    describe(`Group=${ContextGroup.Source}`, () => {
        it.concurrent.each([
            {
                classifications: [
                    SourceAndContagiousDateClassification.SOURCE,
                    SourceAndContagiousDateClassification.CONTAGIOUS,
                ],
            },
            {
                classifications: [
                    SourceAndContagiousDateClassification.BEFORE_SOURCE,
                    SourceAndContagiousDateClassification.CONTAGIOUS,
                ],
            },
            {
                classifications: [
                    SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS,
                    SourceAndContagiousDateClassification.CONTAGIOUS,
                ],
            },
        ])(
            'should return true if ANY date is before/during source period or in overlap: $classifications',
            ({ classifications }) => {
                const result = areDatesInContextGroup(classifications, ContextGroup.Source);
                expect(result).toStrictEqual(true);
            }
        );

        it.concurrent.each([
            { classifications: [SourceAndContagiousDateClassification.CONTAGIOUS] },
            { classifications: [SourceAndContagiousDateClassification.AFTER_CONTAGIOUS] },
            {
                classifications: [
                    SourceAndContagiousDateClassification.CONTAGIOUS,
                    SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
                ],
            },
        ])(
            'should return false if EVERY date is NOT before/during source period and NOT in overlap: $classifications',
            ({ classifications }) => {
                const result = areDatesInContextGroup(classifications, ContextGroup.Source);
                expect(result).toStrictEqual(false);
            }
        );
    });

    describe(`Group=${ContextGroup.Contagious}`, () => {
        it.concurrent.each([
            {
                classifications: [
                    SourceAndContagiousDateClassification.SOURCE_AND_CONTAGIOUS,
                    SourceAndContagiousDateClassification.SOURCE,
                ],
            },
            {
                classifications: [
                    SourceAndContagiousDateClassification.CONTAGIOUS,
                    SourceAndContagiousDateClassification.SOURCE,
                ],
            },
            {
                classifications: [
                    SourceAndContagiousDateClassification.AFTER_CONTAGIOUS,
                    SourceAndContagiousDateClassification.SOURCE,
                ],
            },
        ])(
            'should return true if ANY date is during/after contagious period or in overlap: $classifications',
            ({ classifications }) => {
                const result = areDatesInContextGroup(classifications, ContextGroup.Contagious);
                expect(result).toStrictEqual(true);
            }
        );

        it.concurrent.each([
            { classifications: [SourceAndContagiousDateClassification.BEFORE_SOURCE] },
            { classifications: [SourceAndContagiousDateClassification.SOURCE] },
            {
                classifications: [
                    SourceAndContagiousDateClassification.BEFORE_SOURCE,
                    SourceAndContagiousDateClassification.SOURCE,
                ],
            },
        ])(
            'should return false if EVERY date is NOT during/after contagious period and NOT in overlap: $classifications',
            ({ classifications }) => {
                const result = areDatesInContextGroup(classifications, ContextGroup.Contagious);
                expect(result).toStrictEqual(false);
            }
        );
    });
});

describe('formattedName', () => {
    it('should return - if notificationNamedConsent is false', () => {
        const placeCase = {
            notificationNamedConsent: false,
            name: `${fakerjs.person.firstName()} ${fakerjs.person.lastName()}`,
        };
        const result = formattedName(placeCase);
        expect(result).toStrictEqual('-');
    });

    it('should return name if notificationNamedConsent is true', () => {
        const name = `${fakerjs.person.firstName()} ${fakerjs.person.lastName()}`;
        const placeCase = {
            notificationNamedConsent: true,
            name,
        };
        const result = formattedName(placeCase);
        expect(result).toStrictEqual(name);
    });
});

import { isYes, isNo, isUnknown } from '@/components/form/ts/formOptions';
import { CaseFilterKey } from '@/components/form/ts/formTypes';
import { caseDateRanges } from '../caseDateRanges';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';

describe('caseDateRanges', () => {
    it('should return empty ranges', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
            },
            test: {},
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([]);
    });

    it('should return source and symptomonset range', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
            },
            test: {
                dateOfSymptomOnset: '2021-04-01',
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([
            {
                endDate: new Date('2021-03-30'),
                key: 'source',
                startDate: new Date('2021-03-18'),
            },
            {
                endDate: new Date('2021-04-06'),
                key: 'infectious',
                startDate: new Date('2021-03-30'),
            },
            {
                endDate: new Date('2021-04-01'),
                key: 'symptomonset',
                startDate: new Date('2021-04-01'),
            },
        ]);
    });

    it('should return longer source when no symptoms', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isNo,
            },
            test: {
                dateOfSymptomOnset: '2021-04-01',
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([
            {
                endDate: new Date('2021-03-31'),
                key: 'source',
                startDate: new Date('2021-03-18'),
            },
        ]);
    });

    it('should return longer source when hasSymptoms is not known', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isUnknown,
            },
            test: {
                dateOfSymptomOnset: '2021-04-01',
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([
            {
                endDate: new Date('2021-03-31'),
                key: 'source',
                startDate: new Date('2021-03-18'),
            },
        ]);
    });

    it('should return longer source when hasSymptoms is undefined', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: undefined,
            },
            test: {
                dateOfSymptomOnset: '2021-04-01',
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([
            {
                endDate: new Date('2021-03-31'),
                key: 'source',
                startDate: new Date('2021-03-18'),
            },
        ]);
    });

    it('should return nothing else but test-date when no dateOfSymptomOnset is known', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
            },
            test: {
                dateOfTest: '2021-04-02',
            },
            medication: {
                isImmunoCompromised: isNo,
            },
            underlyingSuffering: {
                hasUnderlyingSufferingOrMedication: isNo,
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([
            {
                startDate: new Date('2021-04-02'),
                endDate: new Date('2021-04-02'),
                key: 'test-date',
            },
        ]);
    });

    it('should return longer infectious period when isImmunoCompromised is true', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isNo,
            },
            immunity: {
                isImmune: isNo,
            },
            test: {
                dateOfTest: '2021-04-02',
                dateOfSymptomOnset: '2021-04-01',
            },
            medication: {
                isImmunoCompromised: isYes,
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([
            {
                startDate: new Date('2021-03-18'),
                endDate: new Date('2021-03-31'),
                key: 'source',
            },
            {
                startDate: new Date('2021-04-02'),
                endDate: new Date('2021-04-07'),
                key: 'infectious',
            },
            {
                startDate: new Date('2021-04-02'),
                endDate: new Date('2021-04-02'),
                key: 'test-date',
            },
        ]);
    });

    it('should return earlier source, infectious and quarantine-end ranges when symptomatic', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
            },
            test: {
                dateOfTest: '2021-04-02',
                dateOfSymptomOnset: '2021-04-01',
            },
            medication: {
                isImmunoCompromised: isNo,
            },
            underlyingSuffering: {
                hasUnderlyingSufferingOrMedication: isNo,
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([
            {
                startDate: new Date('2021-03-18'),
                endDate: new Date('2021-03-30'),
                key: 'source',
            },
            {
                startDate: new Date('2021-03-30'),
                endDate: new Date('2021-04-06'),
                key: 'infectious',
            },
            {
                startDate: new Date('2021-04-01'),
                endDate: new Date('2021-04-01'),
                key: 'symptomonset',
            },
            {
                startDate: new Date('2021-04-02'),
                endDate: new Date('2021-04-02'),
                key: 'test-date',
            },
        ]);
    });

    it('should not return infectious, symptomOnset and quarantine-end when hasSymptoms is not known', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: undefined,
            },
            test: {
                dateOfTest: '2021-04-02',
                dateOfSymptomOnset: '2021-04-01',
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase);

        expect(ranges).toEqual([
            {
                startDate: new Date('2021-03-18'),
                endDate: new Date('2021-03-31'),
                key: 'source',
            },
            {
                startDate: new Date('2021-04-02'),
                endDate: new Date('2021-04-02'),
                key: 'test-date',
            },
        ]);
    });

    it('should filter ranges', () => {
        const covidCase = {
            symptoms: {
                hasSymptoms: isYes,
            },
            test: {
                dateOfTest: '2021-04-02',
                dateOfSymptomOnset: '2021-04-01',
            },
            medication: {
                isImmunoCompromised: isNo,
            },
        } as CovidCaseUnionDTO;

        const ranges = caseDateRanges(covidCase, [CaseFilterKey.TestDate, CaseFilterKey.Source]);

        expect(ranges).toEqual([
            {
                startDate: new Date('2021-03-18'),
                endDate: new Date('2021-03-30'),
                key: 'source',
            },
            {
                startDate: new Date('2021-04-02'),
                endDate: new Date('2021-04-02'),
                key: 'test-date',
            },
        ]);
    });
});

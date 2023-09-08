vi.mock('@/utils/case');
import { isMedicalPeriodInfoIncomplete, isMedicalPeriodInfoNotDefinitive, sourceDates } from '@/utils/case';
import { BcoTypeV1 } from '@dbco/enum';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import { formRuleMet, resetInputDimensions, transformToFormErrors } from '../form';

type FormRuleCombination = {
    formRuleMetProps: {
        storeFragments: AnyObject;
        rule: string;
        value: any;
    };
    sourceDatesMockValue?: {
        endDate: Date;
        startDate: Date;
    };
    isMedicalPeriodInfoIncompleteMockValue?: boolean;
    isMedicalPeriodInfoNotDefinitiveMockValue?: boolean;
    assertion: boolean;
};

describe('formRuleMet', () => {
    const hasValueRules: { value: any; assertion: boolean }[] = [
        { value: null, assertion: false },
        { value: undefined, assertion: false },
        { value: [], assertion: false },
        { value: [1], assertion: true },
        { value: 'a', assertion: true },
        { value: false, assertion: true },
    ];

    // any value except 'standard' counts as extensive
    const hasValuesOrExtensiveBCORules: {
        receivesExtensiveContactTracing: BcoTypeV1 | null | undefined;
        value: any;
        assertion: boolean;
    }[] = [
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_extensive, value: [], assertion: true },
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_other, value: [], assertion: true },
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_unknown, value: [], assertion: true },
        { receivesExtensiveContactTracing: null, value: [], assertion: true },
        { receivesExtensiveContactTracing: undefined, value: [], assertion: true },
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_standard, value: null, assertion: false },
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_standard, value: undefined, assertion: false },
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_standard, value: [], assertion: false },
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_standard, value: 'a', assertion: true },
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_standard, value: ['a'], assertion: true },
        { receivesExtensiveContactTracing: BcoTypeV1.VALUE_standard, value: ['a', 'b'], assertion: true },
    ];

    const combinations: FormRuleCombination[] = [
        {
            formRuleMetProps: {
                storeFragments: {}, // Mocking where this is used, so this should be OK
                rule: 'MEDICAL_PERIOD_INFO_INCOMPLETE',
                value: [1], // not using this in this test, somebody else can sort this out
            },
            sourceDatesMockValue: {
                endDate: new Date('2021-09-30'),
                startDate: new Date('2021-09-01'),
            },
            isMedicalPeriodInfoIncompleteMockValue: true,
            isMedicalPeriodInfoNotDefinitiveMockValue: false, // Not relevant
            assertion: true,
        },
        {
            formRuleMetProps: {
                storeFragments: {}, // Mocking where this is used, so this should be OK
                rule: 'MEDICAL_PERIOD_INFO_INCOMPLETE',
                value: [1], // not using this in this test, somebody else can sort this out
            },
            sourceDatesMockValue: {
                endDate: new Date('2021-09-30'),
                startDate: new Date('2021-09-01'),
            },
            isMedicalPeriodInfoIncompleteMockValue: false,
            isMedicalPeriodInfoNotDefinitiveMockValue: false, // Not relevant
            assertion: false,
        },
        // Case: MEDICAL_PERIOD_INFO_NOT_DEFINITIVE
        {
            formRuleMetProps: {
                storeFragments: {}, // Mocking where this is used, so this should be OK
                rule: 'MEDICAL_PERIOD_INFO_NOT_DEFINITIVE',
                value: [1], // not using this in this test, somebody else can sort this out
            },
            sourceDatesMockValue: {
                endDate: new Date('2021-09-30'),
                startDate: new Date('2021-09-01'),
            },
            isMedicalPeriodInfoIncompleteMockValue: false,
            isMedicalPeriodInfoNotDefinitiveMockValue: false,
            assertion: false,
        },
        {
            formRuleMetProps: {
                storeFragments: {}, // Mocking where this is used, so this should be OK
                rule: 'MEDICAL_PERIOD_INFO_NOT_DEFINITIVE',
                value: [1], // not using this in this test, somebody else can sort this out
            },
            sourceDatesMockValue: {
                endDate: new Date('2021-09-30'),
                startDate: new Date('2021-09-01'),
            },
            isMedicalPeriodInfoIncompleteMockValue: true,
            isMedicalPeriodInfoNotDefinitiveMockValue: false,
            assertion: false,
        },
        {
            formRuleMetProps: {
                storeFragments: {}, // Mocking where this is used, so this should be OK
                rule: 'MEDICAL_PERIOD_INFO_NOT_DEFINITIVE',
                value: [1], // not using this in this test, somebody else can sort this out
            },
            sourceDatesMockValue: {
                endDate: new Date('2021-09-30'),
                startDate: new Date('2021-09-01'),
            },
            isMedicalPeriodInfoIncompleteMockValue: false,
            isMedicalPeriodInfoNotDefinitiveMockValue: true,
            assertion: true,
        },
        {
            formRuleMetProps: {
                storeFragments: {}, // Mocking where this is used, so this should be OK
                rule: 'MEDICAL_PERIOD_INFO_NOT_DEFINITIVE',
                value: [1], // not using this in this test, somebody else can sort this out
            },
            sourceDatesMockValue: {
                endDate: new Date('2021-09-30'),
                startDate: new Date('2021-09-01'),
            },
            isMedicalPeriodInfoIncompleteMockValue: true,
            isMedicalPeriodInfoNotDefinitiveMockValue: true,
            assertion: false,
        },
        ...hasValueRules.map((hasValueRule) => {
            return {
                formRuleMetProps: {
                    storeFragments: {},
                    rule: 'HAS_VALUES',
                    value: hasValueRule.value,
                },
                assertion: hasValueRule.assertion,
            };
        }),
        ...hasValuesOrExtensiveBCORules.map((hasValuesOrExtensiveBCORule) => {
            return {
                formRuleMetProps: {
                    storeFragments: {
                        extensiveContactTracing: {
                            receivesExtensiveContactTracing:
                                hasValuesOrExtensiveBCORule.receivesExtensiveContactTracing,
                        },
                    },
                    rule: 'HAS_VALUES_OR_EXTENSIVE_BCO',
                    value: hasValuesOrExtensiveBCORule.value,
                },
                assertion: hasValuesOrExtensiveBCORule.assertion,
            };
        }),
    ];

    combinations.forEach((combination, index) => {
        it(`should return ${combination.assertion} for ${combination.formRuleMetProps.rule} on test number ${index}`, () => {
            vi.resetAllMocks();
            (sourceDates as Mock).mockReturnValueOnce(combination.sourceDatesMockValue);
            (isMedicalPeriodInfoIncomplete as Mock).mockReturnValueOnce(
                combination.isMedicalPeriodInfoIncompleteMockValue
            );
            (isMedicalPeriodInfoNotDefinitive as Mock).mockReturnValueOnce(
                combination.isMedicalPeriodInfoNotDefinitiveMockValue
            );

            expect(
                formRuleMet(
                    combination.formRuleMetProps.storeFragments,
                    combination.formRuleMetProps.rule,
                    combination.formRuleMetProps.value
                )
            ).toBe(combination.assertion);
        });
    });
});

describe('resetInputDimensions', () => {
    it('should reset an input width and height to empty string', () => {
        const input = document.createElement('input');
        input.style.width = '500px';
        input.style.height = '200px';

        resetInputDimensions(input);

        expect(input.style.width).toBe('');
        expect(input.style.height).toBe('');
    });

    it('should reset a textarea width and height to empty string', () => {
        const input = document.createElement('textarea');
        input.style.width = '500px';
        input.style.height = '200px';

        resetInputDimensions(input);

        expect(input.style.width).toBe('');
        expect(input.style.height).toBe('');
    });
});

describe('transformFormErrors', () => {
    it('should transform all field errors to form warnings in JSON', () => {
        const inputErrors = {
            field: ['Error1', 'Error2'],
        };

        const formErrors = transformToFormErrors(inputErrors);

        expect(formErrors).toEqual({ field: '{"warning":["Error1","Error2"]}' });
    });
});

import { isEnum } from '../enum';

describe('Utils/isEnum', () => {
    it('should return true when value is one of enum values', () => {
        enum TestEnum {
            A = 'A',
            B = 'B',
            C = 'C',
        }

        expect(isEnum(TestEnum)('A')).toBeTruthy();
        expect(isEnum(TestEnum)(TestEnum.A)).toBeTruthy();
    });
    it('should return true when value is one of enum values', () => {
        enum TestEnum {
            A = 'A',
            B = 'B',
            C = 'C',
        }

        expect(isEnum(TestEnum)('D')).toBeFalsy();
    });
});

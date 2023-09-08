import { lcFirst, removeAllExceptAlphanumeric, removeSpecialCharacters } from '../string';

describe('lcFirst', () => {
    it('should return the string with the first character as lowercase', () => {
        expect(lcFirst('ABCDEF')).toBe('aBCDEF');
    });
});

describe('removeSpecialCharacters', () => {
    it('should not remove numbers', () => {
        expect(removeSpecialCharacters('12!@#$%^&*()34')).toBe('1234');
    });
    it('should not remove alphabetical characters', () => {
        expect(removeSpecialCharacters('ab!@#$%^&*()cd')).toBe('abcd');
    });
    it('should not remove dashes', () => {
        expect(removeSpecialCharacters('abc-def-ghi')).toBe('abc-def-ghi');
    });
    it('should remove all special characters besides numbers, alphabetical and dashes', () => {
        expect(removeSpecialCharacters('1#2$3-a@b#c-d^e&f')).toBe('123-abc-def');
    });
});

describe('removeAllExceptNumAlpha', () => {
    it('should not remove numbers', () => {
        expect(removeAllExceptAlphanumeric('12!@#$%^&*()34')).toBe('1234');
    });
    it('should not remove alphabetical characters', () => {
        expect(removeAllExceptAlphanumeric('$%ab!@#$%^&*()cd$%')).toBe('abcd');
    });
    it('should remove dashes', () => {
        expect(removeAllExceptAlphanumeric('abc-def-ghi')).toBe('abcdefghi');
    });
});

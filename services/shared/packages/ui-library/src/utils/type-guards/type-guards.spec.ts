import { isNotNull } from './type-guards';

describe('type-guards', () => {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    it.each<[Record<string, unknown> | string | string[] | undefined | null, boolean]>([
        [{}, true],
        ['array', true],
        [['string'], true],
        [undefined, true],
        [null, false],
    ])('should return whether value %j is not null when given', (value, exptectedIsNotNull) => {
        expect(isNotNull(value)).toBe(exptectedIsNotNull);
    });
});

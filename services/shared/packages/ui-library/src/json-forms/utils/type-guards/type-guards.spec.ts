import type { FormData, JsonSchemaType } from '../../types';
import { isFormData, isSchemaType } from './type-guards';

describe('type-guards', () => {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    it.each<[FormData | any, boolean]>([
        [{ $links: {} }, true],
        [{ $validationErrors: [] }, false],
        [{ links: {} }, false],
        [undefined, false],
        [null, false],
        [false, false],
        [true, false],
        ['{ $links: {} }', false],
    ])('should return whether data %j is considered of type FormData', (formData, expectedIsFormData) => {
        expect(isFormData(formData)).toBe(expectedIsFormData);
    });

    it.each<[JsonSchemaType | string | string[] | undefined, JsonSchemaType[], boolean]>([
        ['string', ['string'], true],
        ['string', ['string', 'array', 'boolean', 'null'], true],
        [['string'], ['string'], false],
        [undefined, ['string'], false],
        ['array', ['string'], false],
        ['array', ['null'], false],
        ['array', ['null', 'array'], true],
        [['null', 'array'], ['null', 'array'], false],
        [null as any, ['null', 'array'], false], // eslint-disable-line @typescript-eslint/no-explicit-any
        [1 as any, ['number'], false], // eslint-disable-line @typescript-eslint/no-explicit-any
        [true as any, ['null', 'array', 'boolean'], false], // eslint-disable-line @typescript-eslint/no-explicit-any
    ])(
        "should return whether type value %j is one of the JsonSchemaType's %j when passing an array of types",
        (type, types, exptectedIsType) => {
            expect(isSchemaType(type, types)).toBe(exptectedIsType);
        }
    );

    it.each<[JsonSchemaType | string | string[] | undefined, JsonSchemaType, boolean]>([
        ['string', 'string', true],
        ['string', 'array', false],
        [['string'], 'string', false],
        [undefined, 'string', false],
        ['array', 'string', false],
        ['array', 'null', false],
        ['array', 'array', true],
        [['null', 'array'], 'null', false],
        [null as any, 'null', false], // eslint-disable-line @typescript-eslint/no-explicit-any
        [1 as any, 'number', false], // eslint-disable-line @typescript-eslint/no-explicit-any
        [true as any, 'boolean', false], // eslint-disable-line @typescript-eslint/no-explicit-any
    ])(
        'should return whether type value %j is the JsonSchemaType %j when passing a single type',
        (type, types, exptectedIsType) => {
            expect(isSchemaType(type, types)).toBe(exptectedIsType);
        }
    );
});

import { inject, ref } from 'vue';
import type { ControlBindings, FormError } from '../../types';
import { getErrorAt } from '@jsonforms/core';
import type { Mock } from 'vitest';
import { useErrors } from './use-errors';
import { faker } from '@faker-js/faker';

vi.mock('@jsonforms/core', () => ({
    getErrorAt: vi.fn(() => vi.fn()),
    getTranslator: vi.fn(() => vi.fn(() => vi.fn())),
    getErrorTranslator: vi.fn(() => vi.fn(() => (error: FormError) => error.message)),
}));

const errorMessage = faker.lorem.sentence();
const errorMessage2 = faker.lorem.sentence();

describe('use-errors', () => {
    beforeAll(() => {
        (inject as Mock).mockImplementation((key: symbol | string) => {
            switch (key.toString()) {
                case 'jsonforms':
                    return { jsonforms: {} };
            }
            return undefined;
        });
    });

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    it.each<[any[], string[]]>([
        [[], []],
        [[errorMessage], [errorMessage]],
        [[errorMessage, undefined], [errorMessage]],
        [[undefined, errorMessage], [errorMessage]],
        [
            [undefined, errorMessage2, null, 2, errorMessage],
            [errorMessage2, errorMessage],
        ],
        [
            ['', undefined, errorMessage],
            ['', errorMessage],
        ],
    ])(
        "returns a filtered list of errors that removes any values that aren't strings",
        (errorMessages, expectedErrorMessages) => {
            const control = ref({
                path: faker.lorem.word(),
                rootSchema: {},
            } as unknown as ControlBindings);

            (getErrorAt as Mock).mockImplementationOnce(() => () => errorMessages.map((message) => ({ message })));

            const errors = useErrors(control);

            expect(errors.value).toEqual(expectedErrorMessages);
            expect(getErrorAt).toHaveBeenCalledWith(control.value.path, control.value.rootSchema);
        }
    );

    it('throws when `jsonforms` was not provided', () => {
        const control = ref({
            path: faker.lorem.word(),
            rootSchema: {},
        } as unknown as ControlBindings);

        (getErrorAt as Mock).mockImplementationOnce(() => () => []);
        (inject as Mock).mockImplementation(vi.fn());

        expect(() => {
            useErrors(control);
        }).toThrowError('jsonforms is not defined');
    });
});

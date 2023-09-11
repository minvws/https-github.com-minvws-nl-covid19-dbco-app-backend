import { faker } from '@faker-js/faker';
import type { ControlElement } from '@jsonforms/core';
import { ref } from 'vue';
import type { CellBindings } from '../../types';
import { useUiOptions } from './use-ui-options';

describe('use-ui-options', () => {
    it.each<[ControlElement, GenericObject]>([
        [undefined as unknown as ControlElement, {}],
        [{} as ControlElement, {}],
        [{ scope: faker.lorem.word(), type: 'Control' }, {}],
        [{ scope: faker.lorem.word(), type: 'Control' }, {}],
        [{ scope: faker.lorem.word(), type: 'Control', options: { multi: true } }, { multi: true }],
        [
            { scope: faker.lorem.word(), type: 'Control', options: { focus: true, placeholder: '' } },
            { focus: true, placeholder: '' },
        ],
    ])(
        'should return the uiOptions: given cell or control with uischema %j it should return the uiOptions %j ',
        (uischema, expectedUiOptions) => {
            const cellOrControl = ref({ uischema } as unknown as CellBindings);
            expect(useUiOptions(cellOrControl).value).toEqual(expectedUiOptions);
        }
    );
});

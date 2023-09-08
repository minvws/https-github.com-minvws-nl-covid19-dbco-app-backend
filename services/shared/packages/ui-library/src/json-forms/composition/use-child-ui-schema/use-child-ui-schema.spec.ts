import { ref } from 'vue';
import { findUISchema } from '@jsonforms/core';
import type { ArrayControlBindings } from '../../types';
import type { Mock } from 'vitest';
import { useChildUiSchema } from './use-child-ui-schema';

vi.mock('@jsonforms/core', () => ({
    findUISchema: vi.fn(),
}));

describe('useChildUiSchema', () => {
    it('returns all the properties of the original implementation', () => {
        const result = { foo: 'bar' };
        (findUISchema as Mock).mockImplementationOnce(() => result);

        const arrayControl = {
            uischemas: [],
            schema: {},
            uischema: { scope: 'foo', type: 'Control' },
            path: 'foo',
            rootSchema: {},
        } as unknown as ArrayControlBindings;

        const childUiSchema = useChildUiSchema(ref(arrayControl));

        expect(childUiSchema.value).toBe(result);
        expect(findUISchema).toHaveBeenCalledWith(
            arrayControl.uischemas,
            arrayControl.schema,
            arrayControl.uischema.scope,
            arrayControl.path,
            undefined,
            arrayControl.uischema,
            arrayControl.rootSchema
        );
    });
});

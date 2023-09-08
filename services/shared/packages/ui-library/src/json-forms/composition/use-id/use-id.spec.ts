import { inject, ref } from 'vue';
import { useId } from './use-id';
import type { CellBindings } from '../../types';
import type { Mock } from 'vitest';

describe('use-id', () => {
    it.each<[string, string | undefined, string]>([
        ['', undefined, 'json-forms--'],
        ['name', undefined, 'json-forms--name'],
        ['favorites.0.type', undefined, 'json-forms--favorites.0.type'],
        ['', 'foobar', 'foobar--'],
        ['name', 'foobar', 'foobar--name'],
        ['favorites.0.type', 'foobar', 'foobar--favorites.0.type'],
    ])(
        'should return the correct id: given cell or control with path %j and root id %j it should return the id %j ',
        (path, rootId, expectedId) => {
            (inject as Mock).mockImplementationOnce((key: string, defaultValue: unknown) => rootId || defaultValue);
            const cellOrControl = ref({ path } as unknown as CellBindings);
            expect(useId(cellOrControl).value).toBe(expectedId);
        }
    );
});

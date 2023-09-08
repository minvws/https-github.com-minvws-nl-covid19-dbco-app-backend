import { computed, ref } from 'vue';
import { useErrorId } from './use-error-id';
import type { CellBindings } from '../../types';

vi.mock('../use-id/use-id', async () => {
    return {
        useId: vi.fn(() => computed(() => 'json-forms-path')),
    };
});

describe('use-id', () => {
    it('should return the correct error id using the regular id as its base', () => {
        const cellOrControl = ref({} as unknown as CellBindings);
        expect(useErrorId(cellOrControl).value).toBe('json-forms-path--error');
    });
});

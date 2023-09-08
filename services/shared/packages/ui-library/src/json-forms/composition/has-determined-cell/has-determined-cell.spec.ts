import type { ControlBindings } from '../../types';
import { hasDeterminedCell } from './has-determined-cell';
import { ref } from 'vue';

describe('has-determined-cell', () => {
    it('should return false if there are no cell renderers', () => {
        const control: Partial<ControlBindings> = {
            cells: [],
        };
        const hasCell = hasDeterminedCell(ref(control as ControlBindings));
        expect(hasCell).toBe(false);
    });

    it('should return true if there are a cell matches', () => {
        const control: Partial<ControlBindings> = {
            cells: [{ cell: null, tester: () => 1 }],
        };
        const hasCell = hasDeterminedCell(ref(control as ControlBindings));
        expect(hasCell).toBe(true);
    });

    it('should return false if there are a cell matches with -1', () => {
        const control: Partial<ControlBindings> = {
            cells: [{ cell: null, tester: () => -1 }],
        };
        const hasCell = hasDeterminedCell(ref(control as ControlBindings));
        expect(hasCell).toBe(false);
    });
});

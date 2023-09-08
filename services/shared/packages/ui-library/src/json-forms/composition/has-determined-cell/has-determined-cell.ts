import { maxBy } from 'lodash';
import type { Ref } from 'vue';
import type { ControlBindings, ControlElementCore } from '../../types';

/**
 * Checks whether a cell has been determined by a cell tester.
 * Based on the implementation of the JsonForms Vue 2 renderer set.
 * @see @jsonforms/vue2/src/components/DispatchCell.vue
 */
export function hasDeterminedCell(control: Ref<ControlBindings>, config?: any) {
    const { rootSchema, cells, schema } = control.value;
    const uischema = control.value.uischema as ControlElementCore;

    const testerContext = { rootSchema, config };
    const determinedCell = maxBy(cells, (cell) => cell.tester(uischema, schema, testerContext));

    return determinedCell !== undefined && determinedCell.tester(uischema, schema, testerContext) !== -1;
}

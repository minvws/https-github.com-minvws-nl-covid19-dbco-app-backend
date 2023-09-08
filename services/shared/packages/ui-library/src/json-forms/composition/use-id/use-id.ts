import type { Ref } from 'vue';
import { computed } from 'vue';
import type { CellBindings, ControlBindings } from '../../types';
import { injectRootId } from '../../core/JsonFormsBase/provide';

export const useId = <T extends ControlBindings | CellBindings>(controlOrCell: Ref<T>) => {
    const { rootId } = injectRootId();
    return computed(() => `${rootId || 'json-forms'}--${controlOrCell.value.path}`);
};

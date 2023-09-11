import type { Ref } from 'vue';
import { computed } from 'vue';
import type { CellBindings, ControlBindings } from '../../types';
import { useId } from '../use-id/use-id';

export const useErrorId = <T extends ControlBindings | CellBindings>(controlOrCell: Ref<T>) => {
    const id = useId(controlOrCell);
    return computed(() => `${id.value}--error`);
};

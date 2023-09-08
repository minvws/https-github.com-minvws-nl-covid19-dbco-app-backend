import type { Ref } from 'vue';
import { computed } from 'vue';

type UiElement = {
    uischema: {
        options?: Record<string, unknown> | unknown;
    };
};

export const useUiOptions = <T extends UiElement, UiOptions = T['uischema']['options']>(controlOrCell: Ref<T>) =>
    computed(() => (controlOrCell.value.uischema?.options || {}) as NonNullable<UiOptions>);

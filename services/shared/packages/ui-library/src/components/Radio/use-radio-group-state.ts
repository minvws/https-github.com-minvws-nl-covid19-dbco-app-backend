import type { Ref } from 'vue';
import { ref, computed, inject, provide } from 'vue';
import type { RadioVariant } from './radio-props';
import { uniqueId } from 'lodash';

type RadioGroupProps = {
    name: Ref<string | undefined>;
    value: Ref<string | undefined>;
    variant: Ref<RadioVariant | undefined>;
};

const injectionKeys = {
    variant: Symbol('variant'),
    value: Symbol('value'),
    name: Symbol('name'),
};

export function provideRadioGroupState({ name: radioGroupName, value, variant }: RadioGroupProps) {
    const name = computed(() => radioGroupName.value || uniqueId('radio-group-'));

    provide(injectionKeys.variant, variant);
    provide(injectionKeys.value, value);
    provide(injectionKeys.name, name);

    return { value, variant };
}

export function injectRadioGroupState() {
    const name = inject(injectionKeys.name, ref()) as Ref<string | undefined>;
    const value = inject(injectionKeys.value, ref()) as Ref<string | undefined>;
    const variant = inject(injectionKeys.variant, ref()) as Ref<RadioVariant | undefined>;

    return { name, value, variant };
}

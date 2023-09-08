<template>
    <component v-tw-merge class="tw-reset-fieldset" :is="as" v-on="$listeners">
        <slot />
    </component>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, toRefs } from 'vue';
import { provideRadioGroupState } from './use-radio-group-state';
import Radio from '../Radio/Radio.vue';

type Tag = 'fieldset' | 'div';

export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'fieldset' },
        value: { type: String },
        variant: Radio.props.variant,
        name: Radio.props.name,
    },
    emits: {
        /* c8 ignore next */
        change: (event: ChangeEvent<HTMLInputElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    setup(props) {
        const { name, value, variant } = toRefs(props);
        provideRadioGroupState({ name, value, variant });
        return {};
    },
});
</script>

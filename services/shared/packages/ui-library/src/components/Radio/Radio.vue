<!-- eslint-disable vuejs-accessibility/no-autofocus -->
<template>
    <component
        :is="variant === 'plain' ? 'RadioPlain' : variant === 'button' ? 'RadioButton' : 'RadioSwitch'"
        :id="radioId"
        :aria-label="ariaLabel"
        :aria-errormessage="ariaErrormessage"
        :checked="checked"
        :disabled="disabled"
        :required="required"
        :name="name"
        :readonly="readonly"
        :autoFocus="autoFocus"
        :invalid="invalid"
        :value="value"
        v-on="$listeners"
    >
        <slot />
    </component>
</template>

<script lang="ts">
import { uniqueId } from 'lodash';
import type { PropType } from 'vue';
import { computed, defineComponent } from 'vue';
import type { RadioVariant } from './radio-props';
import { props } from './radio-props';
import RadioPlain from './RadioPlain.vue';
import RadioButton from './RadioButton.vue';
import RadioSwitch from './RadioSwitch.vue';
import { injectRadioGroupState } from './use-radio-group-state';

export default defineComponent({
    components: {
        RadioPlain,
        RadioButton,
        RadioSwitch,
    },
    emits: {
        /* c8 ignore next */
        change: (event: ChangeEvent<HTMLInputElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    props: {
        ...props,
        id: { type: String },
        variant: { type: String as PropType<RadioVariant> },
    },
    setup(props) {
        const { id, name: radioName, variant: radioVariant, checked: radioChecked, value: radioValue } = props;
        const { name: radioGroupName, value: radioGroupValue, variant: radioGroupVariant } = injectRadioGroupState();

        const name = computed(() => radioName || radioGroupName.value);
        const variant = computed(() => radioVariant || radioGroupVariant.value || 'plain');
        const checked = computed(
            () => radioChecked || (radioGroupValue.value !== undefined && radioValue === radioGroupValue.value)
        );

        return {
            radioId: id || uniqueId('radio-'),
            name,
            variant,
            checked,
        };
    },
});
</script>

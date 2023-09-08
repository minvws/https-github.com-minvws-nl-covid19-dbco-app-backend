<template>
    <FormLabel :as="as" :for="id" v-if="control.label">
        {{ control.label }}
        <template #extra v-if="control.required">{{ t('label.required') }}</template>
    </FormLabel>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, toRef } from 'vue';
import { FormLabel } from '../../../components';
import type { ControlBindings } from '../../types';
import { useId } from '../../composition';
import { injectTranslation } from '../../core/JsonFormsBase/provide';

export default defineComponent({
    components: {
        FormLabel,
    },
    props: {
        as: FormLabel.props.as,
        control: { type: Object as PropType<ControlBindings>, required: true },
    },
    setup(props) {
        const control = toRef(props, 'control');
        const { t } = injectTranslation();

        return {
            id: useId(control),
            t,
        };
    },
});
</script>

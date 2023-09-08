<template>
    <JsonFormsChild
        @change="handleFormChange"
        :data="control.data"
        :schema="control.schema"
        :uiSchema="uiOptions.detail"
    />
</template>

<script lang="ts">
import { cloneDeep } from 'lodash';
import type { DefineComponent } from 'vue';
import { defineComponent } from 'vue';
import type { FormChangeEvent, FormData, ControlElement } from '../../types';
import { rendererProps } from '@jsonforms/vue2';
import { useJsonFormsControl, useUiOptions } from '../../composition';
import { injectEventBus } from '../../core/JsonFormsBase/provide';

export default defineComponent({
    components: {
        JsonFormsChild: (() => {
            return import('../../core/JsonFormsChild/JsonFormsChild.vue').then((x) => x.default);
        }) as unknown as DefineComponent,
    },
    props: {
        ...rendererProps<ControlElement<'child-form'>>(),
    },
    setup(props) {
        const { control } = useJsonFormsControl<FormData, 'child-form'>(props);
        const { eventBus } = injectEventBus();

        const handleFormChange = ({ data, errors }: FormChangeEvent<FormData>) => {
            const { data: controlData, path } = control.value;
            const formDataClone = cloneDeep(controlData) || {};
            Object.assign(formDataClone, data);
            eventBus.$emit('childFormChange', { path, data: formDataClone, errors });
        };

        return {
            control,
            uiOptions: useUiOptions(control),
            handleFormChange,
        };
    },
});
</script>

<template>
    <JsonFormsChild
        :data="localData"
        :schema="schema"
        :uiSchema="uiSchema"
        :additionalErrors="additionalErrors"
        :i18nResource="i18nResource"
        v-on="{ ...$listeners, change: handleFormChange, childFormChange: handleFormChange }"
    />
</template>

<script lang="ts">
import { cloneDeep, set } from 'lodash';
import { defineComponent, ref, watch } from 'vue';
import type { ChildFormChangeEvent, FormChangeEvent } from '../../types';
import JsonFormsChild from '../JsonFormsChild/JsonFormsChild.vue';
import { emits } from './emits';
import { props } from './props';
import { isChildFormChangeEvent } from '../../utils';
import { provideFormActionHandler } from './provide';

export default defineComponent({
    props,
    emits,
    components: {
        JsonFormsChild,
    },
    setup(props, { emit }) {
        const localData = ref(cloneDeep(props.data));

        provideFormActionHandler({ formActionHandler: props.actionHandler || null });

        function handleFormChange(event: FormChangeEvent | ChildFormChangeEvent) {
            let { data, errors } = event;

            if (isChildFormChangeEvent(event) && event.path !== '') {
                data = cloneDeep(localData.value);
                set(data, event.path, event.data);
                errors = errors.map((error) => ({
                    ...error,
                    instancePath: `/${event.path.replace('.', '/')}${error.instancePath}`,
                }));
            }

            localData.value = data;
            emit('change', { data, errors });
        }

        watch(
            () => props.data,
            (newValue) => {
                localData.value = newValue;
            },
            { deep: true }
        );

        return { localData, handleFormChange };
    },
});
</script>

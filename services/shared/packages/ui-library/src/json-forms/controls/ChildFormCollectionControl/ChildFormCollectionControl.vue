<template>
    <VStack>
        <JsonFormsChild
            v-for="(formData, index) in formCollectionData.items"
            @formLink="handleFormLink"
            @change="(data) => handleChildFormChange(index, data)"
            :key="`form-${index}`"
            :data="formData"
            :schema="formSchema"
            :uiSchema="uiOptions.detail"
        />
        <AddChildForm
            v-if="formCollectionData.$links.create"
            :createRequestConfig="formCollectionData.$links.create"
            :schema="formSchema"
            :uiSchema="uiOptions.detail"
            @create="handleNewItemCreated"
        />
    </VStack>
</template>

<script lang="ts">
import type {
    ControlElement,
    FormChangeEvent,
    FormData,
    FormLinkEvent,
    FormCollectionData,
    ChildFormCollectionJsonSchema,
} from '../../types';
import { rendererProps } from '@jsonforms/vue2';
import { cloneDeep } from 'lodash';
import type { DefineComponent } from 'vue';
import { computed, defineComponent } from 'vue';
import { Button, VStack } from '../../../components';
import AddChildForm from './AddChildForm.vue';
import { useJsonFormsControl, useUiOptions } from '../../composition';
import { injectEventBus } from '../../core/JsonFormsBase/provide';

export default defineComponent({
    components: {
        VStack,
        AddChildForm,
        Button,
        JsonFormsChild: (() => {
            return import('../../core/JsonFormsChild/JsonFormsChild.vue').then((x) => x.default);
        }) as unknown as DefineComponent & { emits: [] },
    },
    props: {
        ...rendererProps<ControlElement<'child-form'>>(),
    },
    setup(props) {
        const { eventBus } = injectEventBus();
        const { control } = useJsonFormsControl<FormCollectionData, 'child-form'>(props);

        const formSchema = computed(
            () => (control.value.schema as ChildFormCollectionJsonSchema).properties.items.items
        );

        const formCollectionData = computed(() => {
            if (!control.value.data) throw new Error('Form collection data is undefined');
            return control.value.data;
        });

        const handleChildFormChange = (index: number, { data, errors }: FormChangeEvent<FormData>) => {
            const formCollectionClone = cloneDeep(formCollectionData.value);
            const item = formCollectionClone.items[index];
            Object.assign(item, data);
            eventBus.$emit('childFormChange', { path: control.value.path, data: formCollectionClone, errors });
        };

        const handleNewItemCreated = (item: FormData) => {
            const formCollectionClone = cloneDeep(formCollectionData.value);
            formCollectionClone.items.push(item);
            eventBus.$emit('childFormChange', { path: control.value.path, data: formCollectionClone, errors: [] });
        };

        const handleFormLink = (event: FormLinkEvent) => {
            eventBus.$emit('formLink', event);
        };

        return {
            control,
            formCollectionData,
            formSchema,
            uiOptions: useUiOptions(control),

            handleChildFormChange,
            handleFormLink,
            handleNewItemCreated,
        };
    },
});
</script>

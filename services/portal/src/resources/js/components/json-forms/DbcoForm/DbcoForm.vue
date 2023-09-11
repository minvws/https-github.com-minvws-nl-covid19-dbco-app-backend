<template>
    <div>
        <JsonForms
            :data="data"
            :schema="schema"
            :uiSchema="uiSchema"
            :actionHandler="formActionHandler"
            @change="handleFormChange"
            @formLink="handleFormLink"
        />

        <b-modal
            id="dbco-child-form"
            title="Edit"
            v-model="showFormModal"
            @ok="closeFormModal"
            @cancel="closeFormModal"
            @hidden="closeFormModal"
        >
            <AsyncDbcoForm v-if="formModalLink" :dataHref="formModalLink" />
        </b-modal>
    </div>
</template>

<script lang="ts">
import type { FormChangeEvent, FormLinkEvent } from '@dbco/ui-library';
import { JsonForms } from '@dbco/ui-library';
import { cloneDeep } from 'lodash';
import { computed, defineComponent, ref, toRef, watch } from 'vue';
import { useFormActionHandler } from './useFormActionHandler';
import AsyncDbcoForm from './AsyncDbcoForm.vue';

const { data, schema, uiSchema } = JsonForms.props;

export default defineComponent({
    components: {
        JsonForms,
        AsyncDbcoForm,
    },
    props: {
        initialData: data,
        schema,
        uiSchema,
    },
    setup(props) {
        const data = ref(cloneDeep(props.initialData));
        const schema = toRef(props, 'schema');
        const uiSchema = toRef(props, 'uiSchema');
        const formModalLink = ref<string | null>(null);
        const showFormModal = computed(() => formModalLink.value !== null);

        const handleFormChange = (event: FormChangeEvent) => {
            const { data: newData } = event;
            data.value = newData;
        };

        const handleFormLink = (event: FormLinkEvent) => {
            const { href } = event;
            formModalLink.value = href;
        };

        function closeFormModal() {
            formModalLink.value = null;
        }

        watch(
            () => props.initialData,
            (newValue) => {
                data.value = cloneDeep(newValue);
            },
            { deep: true }
        );

        const { formActionHandler } = useFormActionHandler();

        return {
            data,
            schema,
            uiSchema,
            formActionHandler,
            handleFormChange,
            handleFormLink,
            showFormModal,
            closeFormModal,
            formModalLink,
        };
    },
});
</script>

<template>
    <BModal
        title="(Geplande) test toevoegen"
        ok-title="Toevoegen"
        cancel-title="Annuleren"
        visible
        ref="modal"
        :ok-disabled="isLoading"
        @ok="onOkButtonHandler"
        @cancel="cancelModal"
        @hidden="cancelModal"
    >
        <div class="tw-relative">
            <div class="tw-absolute tw-flex tw-items-center tw-justify-center tw-w-full tw-h-full" v-if="isLoading">
                <BSpinner centered variant="primary" data-testid="loading-spinner" />
            </div>
            <div class="form-container mx-n3 mb-4" :class="{ 'tw-opacity-30': isLoading }">
                <FormulateFormWrapper
                    :schema="manualTestResultCreateFormSchema()"
                    v-model="formValues"
                    :errors="errors"
                    @submit="submitTestResult"
                />
            </div>
        </div>
    </BModal>
</template>
<script lang="ts">
import { computed, defineComponent, ref } from 'vue';
import { manualTestResultCreateFormSchema } from '@/components/form/ts/formSchema';
import type { BModal, BvModalEvent } from 'bootstrap-vue';
import { createTestResult } from '@dbco/portal-api/client/case.api';
import type { CreateManualTestResultFields } from '@dbco/portal-api/case.dto';
import axios from 'axios';
import { isEmpty } from 'lodash';
import useStatusAction, { isPending } from '@/store/useStatusAction';
import { transformToFormErrors } from '@/utils/form';
export type TestResultFormValues = Partial<CreateManualTestResultFields>;
export default defineComponent({
    props: {
        case: {
            type: String,
            required: true,
        },
    },
    emits: ['cancel', 'save'],
    setup({ case: uuid }, { emit }) {
        const formValues = ref<Partial<CreateManualTestResultFields>>({});
        const modal = ref<BModal | null>(null);
        const errors = ref<Record<string, string> | undefined>(undefined);
        const cancelModal = () => emit('cancel');
        const { action: submitTestResult, status } = useStatusAction(
            async (data: Partial<CreateManualTestResultFields>) => {
                try {
                    if (!data || isEmpty(data)) {
                        throw 'Cannot submit empty test result, please fill in form.';
                    }

                    await createTestResult(uuid, {
                        ...data,
                        laboratory: data.laboratory == '' ? undefined : data.laboratory,
                        monsterNumber: data.monsterNumber == '' ? undefined : data.monsterNumber,
                    });
                } catch (e) {
                    // backend errors
                    if (axios.isAxiosError(e) && e.response?.data.errors) {
                        errors.value = transformToFormErrors(e.response.data.errors);
                        throw 'Form validation error';
                    }
                    throw `Unexpected form submission error: ${e}`;
                }
                modal.value?.hide();
                emit('save');
            }
        );
        const isLoading = computed(() => isPending(status.value));
        const onOkButtonHandler = async (event: BvModalEvent) => {
            event.preventDefault();
            await submitTestResult(formValues.value);
        };

        return {
            errors,
            formValues,
            isLoading,
            modal,
            submitTestResult,
            cancelModal,
            onOkButtonHandler,
            manualTestResultCreateFormSchema,
        };
    },
});
</script>

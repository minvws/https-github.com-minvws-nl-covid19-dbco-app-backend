<template>
    <BModal
        id="supervision-modal"
        title="Stel je vraag"
        okTitle="Verstuur vraag"
        @ok.prevent="submitModal"
        @hidden="resetModal"
        ref="modal"
    >
        <div class="form-container">
            <FormulateForm
                id="supervision-form"
                name="supervision-form"
                v-model="formValues"
                :errors="errors"
                @submit="askQuestion"
            >
                <FormulateInput
                    type="select"
                    name="type"
                    :options="expertOptions"
                    label="Wie wil je hulp vragen?"
                    placeholder="Maak een keuze"
                    validation="required|max:250,length"
                    error-behavior="submit"
                    class="w100"
                />
                <FormulateInput
                    type="text"
                    name="phone"
                    label="Telefoonnummer waarop je bereikbaar bent"
                    placeholder="Telefoonnummer"
                    validation="optional|max:25,length"
                    error-behavior="submit"
                    class="w100"
                />
                <FormulateInput
                    type="text"
                    name="subject"
                    label="Onderwerp"
                    placeholder="Beschrijf het onderwerp, zonder persoonlijke gegevens te delen"
                    validation="required|max:250,length"
                    error-behavior="submit"
                    class="w100"
                />
                <FormulateInput
                    type="textarea"
                    name="question"
                    label="Toelichting"
                    placeholder="Beschrijf je vraag, zonder persoonlijke gegevens te delen"
                    validation="required|max:5000,length"
                    error-behavior="submit"
                    rows="3"
                    max-rows="6"
                    :maxlength="maxLength"
                    class="mb-0 w100"
                />
                <small>{{ characterCount }}</small>
            </FormulateForm>
        </div>
    </BModal>
</template>

<script lang="ts">
import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { getAllErrors } from '@/components/form/ts/formRequest';
import { StoreType } from '@/store/storeType';
import { SupervisionActions } from '@/store/supervision/supervisionActions/supervisionActions';
import type { ExpertQuestionTypeV1 } from '@dbco/enum';
import { expertQuestionTypeV1Options } from '@dbco/enum';
import showToast from '@/utils/showToast';
import axios from 'axios';
import type { BModal } from 'bootstrap-vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'SupervisionModal',
    data() {
        return {
            errors: {},
            expertOptions: expertQuestionTypeV1Options,
            formValues: {} as { type?: ExpertQuestionTypeV1; phone?: string; subject?: string; question?: string },
            maxLength: 5000,
        };
    },
    computed: {
        characterCount() {
            return `${this.formValues.question?.length || 0}/${this.maxLength}`;
        },
        uuid() {
            return this.$store.getters[`${StoreType.INDEX}/uuid`];
        },
    },
    methods: {
        submitModal() {
            this.$formulate.submit('supervision-form');
        },
        resetModal() {
            this.formValues = {};
            this.errors = {};
        },
        async askQuestion() {
            try {
                const response: ExpertQuestionResponse = await this.$store.dispatch(
                    `${StoreType.SUPERVISION}/${SupervisionActions.ASK_QUESTION}`,
                    {
                        uuid: this.uuid,
                        question: this.formValues,
                    }
                );
                (this.$refs.modal as BModal).hide();
                const expert = expertQuestionTypeV1Options[response.type];
                showToast(`Vraag is verstuurd aan ${expert}`, 'ask-expert-question');
            } catch (error) {
                // handles validation errors returned from BE
                const validationErrors =
                    axios.isAxiosError(error) && error.response ? error.response.data.validationResult : undefined;
                if (validationErrors) {
                    const allErrors = getAllErrors(validationErrors);
                    this.errors = allErrors ? allErrors.errors : {};
                } else {
                    (this.$refs.modal as BModal).hide();
                    showToast('Er is iets fout gegaan', 'ask-expert-question', true);
                }
            }
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

small {
    color: $dark-grey;
    float: right;
    font-size: 0.75rem;
}
</style>

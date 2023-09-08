<template>
    <BModal
        v-if="cases && cases.length"
        title="Een of meerdere cases sluiten"
        ok-title="Case(s) sluiten"
        ok-variant="primary"
        cancel-title="Annuleren"
        cancel-variant="outline-primary"
        @ok.prevent="onConfirm"
        @hidden="resetModal"
        ref="modal"
        :cancelDisabled="isLoading"
        :okDisabled="isLoading"
    >
        <BForm>
            <BFormGroup label="Toelichting" label-for="archive-text-area">
                <BFormTextarea
                    required
                    v-model="archiveNote"
                    id="archive-text-area"
                    placeholder="Licht toe waarom je de case sluit. Bijvoorbeeld omdat er geen tijd is om de dossiers te checken. Of omdat de index onbereikbaar is."
                    rows="3"
                    :disabled="isLoading"
                    @input="showRequiredMessage = false"
                />
                <BFormInvalidFeedback class="invalid-feedback" :state="!showRequiredMessage">
                    <i class="icon icon--error-warning" />
                    Het invullen van de toelichting is verplicht.
                </BFormInvalidFeedback>
            </BFormGroup>
            <BFormCheckbox v-model="sendOsirisNotifiction">Stuur een Osiris melding</BFormCheckbox>
            <FormInfo v-if="!sendOsirisNotifiction" infoType="warning">
                Elke case moet in Osiris gemeld worden<br />
                Als je het vinkje weghaalt, moet je dit handmatig doen.
            </FormInfo>
        </BForm>
    </BModal>
</template>

<script lang="ts">
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { caseApi } from '@dbco/portal-api';
import type { BModal } from 'bootstrap-vue';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'CovidCaseArchiveModal',
    components: { FormInfo },
    props: {
        cases: {
            type: Array as PropType<string[]>,
            required: true,
        },
    },
    data() {
        return {
            archiveNote: '',
            showRequiredMessage: false,
            sendOsirisNotifiction: true,
            isLoading: false,
        };
    },
    methods: {
        async onConfirm() {
            if (!this.archiveNote.trim().length) {
                this.showRequiredMessage = true;
                this.isLoading = false;
                return;
            }

            this.isLoading = true;

            await caseApi.archiveCases(this.cases, this.archiveNote, this.sendOsirisNotifiction);
            this.$emit('archiveDone');

            this.resetModal();
        },
        resetModal() {
            this.archiveNote = '';
            this.showRequiredMessage = false;
            this.sendOsirisNotifiction = true;
            this.isLoading = false;
        },
        show() {
            (this.$refs.modal as BModal).show();
        },
        hide() {
            (this.$refs.modal as BModal).hide();
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.form-group {
    margin-bottom: 0;
    textarea {
        color: $black;
        padding: 0.75rem 1rem;
        font-size: 0.875rem; // 14/16
    }
}

.custom-checkbox {
    margin: 1rem 0;
    line-height: 1.5rem;
}
</style>

<template>
    <BModal
        cancel-title="Annuleren"
        cancel-variant="outline-primary"
        ok-title="GGD-regio wijzigen"
        ok-variant="primary"
        @ok.prevent="onConfirm"
        @hidden="onHidden"
        ref="modal"
        title="GGD-regio wijzigen"
    >
        <BForm class="form-container">
            <FormInfo
                class="info-block--lg mb-3"
                :text="
                    generateSafeHtml(
                        '<strong>Let op:</strong> Je kunt hier de case naar een andere GGD-regio verplaatsen. Dit is niet hetzelfde als uitbesteden. Na de verplaatsing kun je de case niet meer inzien of bewerken.'
                    )
                "
                infoType="info"
            />
            <BFormGroup label="GGD-regio">
                <BDropdown
                    id="overwrite-dropdown"
                    :text="current ? current.name : 'Selecteer GGD-regio'"
                    :toggle-class="['d-flex', 'align-items-center', 'justify-content-between']"
                    block
                    lazy
                    variant="outline-primary"
                >
                    <BDropdownItem
                        v-for="organisation in organisations"
                        :key="organisation.uuid"
                        @click="setCurrentOrganisation(organisation)"
                        >{{ organisation.name }}</BDropdownItem
                    >
                </BDropdown>
            </BFormGroup>
            <BFormGroup label="Notitie bij verandering GGD-regio (verplicht)">
                <BFormTextarea
                    v-model="note"
                    placeholder="Vul een toelichting in"
                    rows="3"
                    @input="showRequiredMessage = false"
                />
                <BFormInvalidFeedback class="invalid-feedback" :state="!showRequiredMessage">
                    <i class="icon icon--error-warning" />
                    Het invullen van de toelichting is verplicht.
                </BFormInvalidFeedback>
            </BFormGroup>
        </BForm>
    </BModal>
</template>

<script lang="ts">
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { OrganisationMutations } from '@/store/organisation/organisationMutations/organisationMutations';
import { usePlanner } from '@/store/planner/plannerStore';
import { generateSafeHtml } from '@/utils/safeHtml';
import { StoreType } from '@/store/storeType';
import { defineComponent } from 'vue';
import { BModal } from 'bootstrap-vue';
import { mapActions, mapState } from 'pinia';
import { mapMutations } from 'vuex';
import { mapState as mapVuexState } from '@/utils/vuex';

export default defineComponent({
    name: 'CovidCaseOrganisationEditModal',
    components: {
        BModal,
        FormInfo,
    },
    data() {
        return {
            note: '',
            showRequiredMessage: false,
        };
    },
    computed: {
        ...mapVuexState('organisation', ['current']),
        ...mapState(usePlanner, ['organisations']),
        modal() {
            return this.$refs.modal as BModal;
        },
    },
    methods: {
        ...mapActions(usePlanner, ['changeCaseOrganisation']),
        ...mapMutations(StoreType.ORGANISATION, {
            setCurrentOrganisation: OrganisationMutations.SET_CURRENT,
        }),
        generateSafeHtml,
        onConfirm() {
            if (!this.note.length) {
                this.showRequiredMessage = true;
                return;
            }
            this.changeCaseOrganisation({
                note: this.note,
                organisationUuid: this.current?.uuid,
            }).then(() => {
                this.$emit('changed');
                this.modal.hide();
            });
        },
        onHidden() {
            this.note = '';
            this.showRequiredMessage = false;
            this.setCurrentOrganisation(null);
        },
        show() {
            this.modal.show();
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.form-group {
    ::v-deep {
        legend,
        span {
            font-weight: 500;
        }

        textarea {
            border: 1px solid $lightest-grey;
            color: $black;
            padding: 0.75rem 1rem;
            font-size: 0.875rem; // 14/16
        }

        .invalid-feedback {
            margin-top: 0.5rem;

            i {
                margin-left: 0;
                margin-right: 0.25rem;
            }
        }
    }
}

#overwrite-dropdown {
    ::v-deep {
        button {
            border-color: $lightest-grey;
            color: $black;
            padding: 10px 16px;
            background: none;
        }

        ul {
            width: 100%;
            max-height: 12.5rem;
            overflow: auto;
        }

        .dropdown-item {
            color: $black;
        }
    }
}
</style>

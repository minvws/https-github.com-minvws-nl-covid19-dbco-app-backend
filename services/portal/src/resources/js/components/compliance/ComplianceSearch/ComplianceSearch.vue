<template>
    <div>
        <BTabs v-model="activeTab" nav-class="nav-tabs--in-card mx-3">
            <template>
                <BTab title="Persoonsgegevens">
                    <div class="form-container pt-3">
                        <FormulateForm
                            v-model="nameValues"
                            :schema="searchByNameSchema"
                            @submit="onSearch"
                            @validation="nameValidation"
                        >
                            <BContainer>
                                <BRow class="justify-content-end">
                                    <BCol class="col-auto">
                                        <FormulateInput type="submit" label="Zoeken" :disabled="isNameDisabled" />
                                    </BCol>
                                </BRow>
                            </BContainer>
                        </FormulateForm>
                    </div>
                </BTab>
                <BTab title="Dossiergegevens">
                    <div class="form-container pt-3">
                        <FormulateForm
                            v-model="caseValues"
                            :schema="searchByCaseSchema"
                            @submit="onSearch"
                            @validation="caseValidation"
                        >
                        </FormulateForm>
                    </div>
                </BTab>
            </template>
        </BTabs>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { complianceSearchByNameSchema, complianceSearchByCaseSchema } from '@/components/form/ts/formSchema';
import type { VueFormulateValidationEvent } from '@/components/form/ts/formTypes';

enum SearchTab {
    IndexDetails,
    CaseDetails,
}

interface IndexSearchOptions {
    lastname?: string;
    email?: string;
    dateOfBirth?: string;
    phone?: string;
}

interface CaseSearchOptions {
    identifier?: string;
}

export default defineComponent({
    name: 'ComplianceSearch',
    props: {
        values: {
            type: Object as PropType<IndexSearchOptions & CaseSearchOptions>,
            default: () => ({}),
        },
    },
    data() {
        return {
            activeTab: SearchTab.IndexDetails,
            searchByNameSchema: complianceSearchByNameSchema(),
            nameValues: {} as IndexSearchOptions,
            caseValues: {} as CaseSearchOptions,
            hasNameErrors: false,
            hasCaseErrors: false,
        };
    },
    created() {
        if (this.values.lastname) {
            const { lastname, email, dateOfBirth, phone } = this.values;
            this.nameValues = { lastname, email, dateOfBirth, phone };
            return;
        }

        if (this.values.identifier) {
            this.caseValues = { identifier: this.values.identifier };
            this.activeTab = SearchTab.CaseDetails;
            return;
        }
    },
    computed: {
        isNameDisabled() {
            if (this.hasNameErrors) return true;

            const { lastname, email, dateOfBirth, phone } = this.nameValues;

            return Boolean(!lastname || (!email && !dateOfBirth && !phone));
        },
        searchByCaseSchema() {
            return complianceSearchByCaseSchema(this.caseValues);
        },
    },
    methods: {
        onSearch() {
            const values = this.activeTab === 0 ? this.nameValues : this.caseValues;
            const params = new URLSearchParams();
            Object.entries(values).forEach(([key, value]) => {
                if (!value) return;

                params.set(key, value);
            });

            window.location.assign(`/compliance/search#${params.toString()}`);
            this.$emit('submit', values);
        },
        nameValidation($event: VueFormulateValidationEvent) {
            this.hasNameErrors = $event.hasErrors;
        },
        caseValidation($event: VueFormulateValidationEvent) {
            this.hasCaseErrors = $event.hasErrors;
        },
    },
});
</script>

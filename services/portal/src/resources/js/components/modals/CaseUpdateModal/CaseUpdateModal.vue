<template>
    <BModal
        v-if="caseUpdate"
        id="CaseUpdateModal"
        title="Door de index aangeleverde antwoorden"
        size="xl"
        visible
        @hide="$emit('hide')"
    >
        <BTableSimple class="table-ggd w-100">
            <BThead>
                <BTr>
                    <BTh>
                        <BFormCheckbox v-model="allSelected" :indeterminate="indeterminate" @change="toggleAll" />
                    </BTh>
                    <BTh scope="col" class="text-uppercase"><small>Vraag</small></BTh>
                    <BTh scope="col" class="text-uppercase"><small>Huidig antwoord</small></BTh>
                    <BTh scope="col" class="text-uppercase"><small>Aangeleverd antwoord</small></BTh>
                </BTr>
            </BThead>
            <BTbody v-for="item in items" :key="item.name">
                <BTr>
                    <BTh colspan="4">
                        <h4 class="mb-0">{{ item.label }}</h4>
                    </BTh>
                </BTr>
                <BTr v-for="field in item.fields" :key="field.id">
                    <BTd class="pl-5">
                        <BFormCheckbox
                            v-model="selected"
                            :id="field.id"
                            :name="field.id"
                            v-bind="$as.any({ value: field.id })"
                        />
                    </BTd>
                    <BTd>{{ field.label }}</BTd>
                    <BTd><CaseUpdateModalDisplayValue :value="field.oldDisplayValue" /></BTd>
                    <BTd><CaseUpdateModalDisplayValue :value="field.newDisplayValue" /></BTd>
                </BTr>
            </BTbody>
        </BTableSimple>
        <template slot="modal-footer">
            <span class="flex-grow-1">
                Deze case is {{ $filters.dateFnsFormat(caseUpdate.receivedAt, `dd MMM 'om' H:mm`) }} door de index
                vooringevuld via zelfbco.nl
            </span>
            <BButton variant="primary" @click="submit" :disabled="selected.length === 0">Overnemen</BButton>
        </template>
    </BModal>
</template>

<script lang="ts">
import { caseUpdateApi } from '@dbco/portal-api';
import { defineComponent } from 'vue';
import type { CaseUpdateItem, CaseUpdateItemFragment } from '@dbco/portal-api/caseUpdate.dto';
import CaseUpdateModalDisplayValue from './CaseUpdateModalDisplayValue.vue';

export default defineComponent({
    name: 'CaseUpdateModal',
    components: { CaseUpdateModalDisplayValue },
    props: {
        caseUpdateId: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            caseUpdate: undefined as CaseUpdateItem | undefined,
            selected: [] as string[],
            allSelected: false,
            indeterminate: false,
        };
    },
    async created() {
        const data = await caseUpdateApi.getCaseUpdate(this.uuid, this.caseUpdateId);
        this.caseUpdate = data;
        this.selected = this.allFields;
    },
    methods: {
        async submit() {
            await caseUpdateApi.applyCaseUpdate(this.uuid, this.caseUpdateId, { fieldIds: this.selected });
            await this.$store.dispatch('index/LOAD', this.uuid);

            this.$bvModal.hide('CaseUpdateModal');
            this.$emit('submitted');
        },
        toggleAll(checked: boolean) {
            this.selected = checked ? this.allFields : [];
        },
        setIndeterminate(newValue: string[]) {
            this.allSelected = newValue.length === this.allFields.length;
            this.indeterminate = newValue.length > 0 && !this.allSelected;
        },
    },
    watch: {
        selected(newValue) {
            this.setIndeterminate(newValue);
        },
    },
    computed: {
        items() {
            const fragments: CaseUpdateItemFragment[] = [];
            if (this.caseUpdate) {
                this.caseUpdate.fragments.forEach((fragment) => fragments.push(fragment));
                this.caseUpdate.contacts?.forEach((contact) => {
                    contact.fragments.forEach((fragment) => fragments.push(fragment));
                });
            }
            return fragments;
        },
        allFields() {
            const allFields: string[] = [];
            if (this.caseUpdate) {
                this.caseUpdate.fragments.forEach((fragment) => {
                    fragment.fields.forEach((field) => allFields.push(field.id));
                });
                this.caseUpdate.contacts?.forEach((contact) => {
                    contact.fragments.forEach((fragment) => {
                        fragment.fields.forEach((field) => allFields.push(field.id));
                    });
                });
            }
            return allFields;
        },
        uuid() {
            return this.$store.getters['index/uuid'];
        },
    },
});
</script>

<style lang="scss" scoped>
::v-deep {
    .modal-footer {
        padding: 0.75rem;
        background: #f6f6fa;
        border-top: 1px solid #e5e3ef;

        .btn {
            flex: none;
        }
    }
}
</style>

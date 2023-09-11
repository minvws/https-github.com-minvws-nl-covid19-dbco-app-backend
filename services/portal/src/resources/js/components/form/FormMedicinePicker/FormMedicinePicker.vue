<template>
    <div>
        <FormMedicineDatalist />
        <BTableSimple class="table-form table-ggd w-100">
            <BThead>
                <BTr>
                    <BTh scope="col">Naam medicijn</BTh>
                    <BTh scope="col">Toelichting (optioneel)</BTh>
                    <BTh scope="col">Afweeronderdrukkend (overleg eerst met arts)</BTh>
                    <BTh scope="col"></BTh>
                </BTr>
            </BThead>
            <BTbody>
                <BTr v-for="(model, index) in values" :key="index">
                    <BTd>
                        <BFormInput
                            class="px-0"
                            v-model="model.name"
                            placeholder="Vul medicijn in"
                            @blur="addItem"
                            :disabled="disabled"
                            :state="isValid(index) ? null : false"
                            list="medicine-list"
                            data-testid="medicine-name"
                        />
                    </BTd>
                    <BTd>
                        <BFormInput
                            v-model="model.remark"
                            placeholder="Toelichting, bijv. dosering, reden van gebruik"
                            data-testid="medicine-remark"
                            :disabled="disabled"
                            maxlength="5000"
                        />
                    </BTd>
                    <BTd>
                        <BFormInput
                            :disabled="disabled"
                            v-model="model.knownEffects"
                            data-testid="medicine-known-effects"
                        />
                    </BTd>
                    <BTd class="text-center td-icon" v-if="!disabled">
                        <BButton v-if="model.name" variant="link" @click="deleteMedicine(index)"
                            ><i class="d-inline-block icon icon--delete icon--m0"></i
                        ></BButton>
                    </BTd>
                </BTr>
            </BTbody>
        </BTableSimple>
    </div>
</template>

<script lang="ts">
import FormMedicineDatalist from '../FormMedicineDatalist/FormMedicineDatalist.vue';
import { defineComponent } from 'vue';
import type { MedicineCommon } from '@dbco/schema/covidCase/medication/medicineCommon';

export default defineComponent({
    name: 'FormMedicinePicker',
    components: { FormMedicineDatalist },
    props: {
        disabled: {
            type: Boolean,
        },
        context: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            values: (this.context.model || []) as MedicineCommon[],
        };
    },
    created() {
        // Adds a new empty line
        this.values.push({
            name: '',
            remark: null,
            knownEffects: null,
        });
    },
    methods: {
        addItem() {
            if (this.values[this.values.length - 1].name) {
                // can't have a table without a newline now can we
                this.values.push({
                    name: '',
                    remark: null,
                    knownEffects: null,
                });
            }
        },
        deleteMedicine(index: number) {
            const values = [...this.values];
            values.splice(index, 1);

            this.values = values;
            this.context.model = Object.assign(
                {},
                values.filter((val) => val.name)
            );
            this.$emit('change');
        },
        isValid(index: number) {
            const item = this.values[index];

            // Show error when name hasn't been filled
            if (!item.name && (item.remark || item.knownEffects)) {
                return false;
            }

            return true;
        },
    },
    watch: {
        values: {
            handler(values) {
                this.context.model = Object.assign(
                    {},
                    values.filter((val: MedicineCommon) => val.name)
                );
            },
            deep: true,
        },
    },
});
</script>
<style scoped></style>

<template>
    <BDropdown variant="link" text="small" class="dbco-select" :right="right" :disabled="disabled">
        <template #button-content>
            {{ currentOption }}
            <span v-show="!currentOption">Kies categorie</span>
        </template>
        <BDropdownItem v-for="(option, index) in options" :key="index" @click="select(option)">
            <strong>{{ option.title }}</strong>
            <p>{{ option.subtitle }}</p>
        </BDropdownItem>
        <BDropdownDivider />
        <BDropdownForm>
            <BFormGroup label="" label-for="dropdown-form-add-on" @submit.stop.prevent>
                <label for="textarea-nature">Optioneel: wat is er tijdens het contact gebeurd?</label>
                <BFormTextarea
                    @input="$emit('update:nature', natureValue)"
                    v-model="natureValue"
                    id="textarea-nature"
                    rows="2"
                    placeholder="Beschrijf situatie"
                />
            </BFormGroup>
        </BDropdownForm>
        <slot></slot>
    </BDropdown>
</template>

<script>
export default {
    name: 'DbcoCategorySelect',
    props: {
        value: String,
        nature: String,
        right: {
            // Position dropdown from right side
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            natureValue: this.nature,
            options: [
                {
                    value: '1',
                    title: '1 - Huisgenoot',
                    subtitle: 'Leven in dezelfde woonomgeving en langdurig contact op minder dan 1,5 meter',
                },
                { value: '2a', title: '2A - Nauw contact', subtitle: 'Opgeteld meer dan 15 minuten binnen 1,5 meter' },
                {
                    value: '2b',
                    title: '2B - Nauw contact',
                    subtitle: 'Opgeteld minder dan 15 minuten binnen 1,5 meter, met hoogrisico contact',
                },
                {
                    value: '3a',
                    title: '3A - Overig contact',
                    subtitle: 'Opgeteld meer dan 15 minuten op meer dan 1,5 meter, in dezelfde ruimte',
                },
                {
                    value: '3b',
                    title: '3B - Overig contact',
                    subtitle:
                        'Opgeteld minder dan 15 minuten binnen 1,5 meter, zonder hoogrisico contact, in dezelfde ruimte of buiten',
                },
            ],
        };
    },
    watch: {
        nature(newVal) {
            // We need a second model because we have a field piggybacking inside the control
            // We do a manual 'model' cycle using a prop/emit loop
            this.natureValue = newVal;
        },
    },
    computed: {
        currentOption: function () {
            for (var i = 0; i < this.options.length; i++) {
                if (this.options[i].value == this.value) {
                    return this.options[i].value.toUpperCase(); // design says to show '2A' and not the full title
                }
            }
        },
    },
    methods: {
        select(selectedOption) {
            this.$emit('input', selectedOption.value);
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.dropdown-divider {
    margin: 0;
}

label {
    font-weight: 500;
}

.dropdown-item {
    p {
        color: $light-grey;
        margin-bottom: 0;
    }
}
</style>

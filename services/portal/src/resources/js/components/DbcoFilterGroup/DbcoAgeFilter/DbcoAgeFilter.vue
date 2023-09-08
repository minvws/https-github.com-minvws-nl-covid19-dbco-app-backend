<template>
    <div class="filter">
        <label for="dateOfBirth" class="m-0">{{ $t('components.dropDownAgeFilter.ageLabel') }}</label>
        <BDropdown
            id="dateOfBirth"
            ref="dropDownRef"
            :text="ageLabel"
            variant="link"
            toggle-class="px-2 text-primary text-decoration-none"
        >
            <div class="age-filters d-flex align-items-center px-2 pb-2">
                <BForm>
                    <BInputGroup>
                        <div>
                            <label for="ageMin">{{ $t('components.dropDownAgeFilter.ageLabel') }}</label>
                            <BFormInput
                                @keyup="ageMinFormat"
                                type="number"
                                id="ageMin"
                                v-model="ageMinRef"
                                :placeholder="`${ageMin}`"
                                :class="{ invalid: !ageMinIsValid }"
                            />
                        </div>
                        <div>
                            <label for="ageMax">{{ $t('components.dropDownAgeFilter.ageEnd') }}</label>
                            <BFormInput
                                @keyup="ageMaxFormat"
                                type="number"
                                id="ageMax"
                                v-model="ageMaxRef"
                                :placeholder="`${ageMax}`"
                                :class="{ invalid: !ageMaxIsValid }"
                            />
                        </div>
                    </BInputGroup>

                    <BInputGroup>
                        <BButton id="resetFilterButton" variant="secondary" @click="resetFilter()">
                            {{ $t('components.dropDownAgeFilter.resetButton') }}
                        </BButton>
                        <BButton
                            id="sendAgeFilterButton"
                            :disabled="!ageMinIsValid || !ageMaxIsValid"
                            variant="primary"
                            @click="sendAgeFilter()"
                        >
                            {{ $t('components.dropDownAgeFilter.filterButton') }}
                        </BButton>
                    </BInputGroup>
                </BForm>
            </div>
        </BDropdown>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, ref } from 'vue';
import { BDropdown, BFormInput } from 'bootstrap-vue';
import type { TranslateResult } from 'vue-i18n';

export default defineComponent({
    name: 'DbcoAgeFilter',
    props: {
        ageLabel: { type: String as PropType<TranslateResult | string>, required: true },
    },
    components: {
        BFormInput,
        BDropdown,
    },
    emits: ['selected'],
    setup(props, ctx) {
        const ageMinIsValid = ref<boolean>(true);
        const ageMaxIsValid = ref<boolean>(true);
        const ageMin = 0;
        const ageMax = 120;
        const dropDownRef = ref<BDropdown | null>(null);
        const ageMinRef = ref<string>(ageMin.toString());
        const ageMaxRef = ref<string>(ageMax.toString());

        const ageMinFormat = () => {
            const value = parseInt(ageMinRef.value);
            const maxValue = parseInt(ageMaxRef.value);

            if (value < ageMin || value > maxValue) return (ageMinIsValid.value = false);
            ageMinIsValid.value = true;
        };

        const ageMaxFormat = () => {
            const value = parseInt(ageMaxRef.value);
            const minValue = parseInt(ageMinRef.value);

            if (value > ageMax || value < minValue) return (ageMaxIsValid.value = false);
            ageMaxIsValid.value = true;
        };

        const resetFilter = () => {
            ageMinRef.value = ageMin.toString();
            ageMaxRef.value = ageMax.toString();
            ageMinIsValid.value = true;
            ageMaxIsValid.value = true;
            dropDownRef.value?.hide();
            ctx.emit('selected', { min: null, max: null });
        };

        const sendAgeFilter = () => {
            if (!ageMinRef.value && !ageMaxRef.value) {
                resetFilter();
            } else {
                const value = {
                    min: ageMinRef.value ? parseInt(ageMinRef.value) : ageMin,
                    max: ageMaxRef.value ? parseInt(ageMaxRef.value) : ageMax,
                };
                dropDownRef.value?.hide();
                ctx.emit('selected', value);
            }
        };

        return {
            ageMax,
            ageMaxFormat,
            ageMaxIsValid,
            ageMaxRef,
            ageMin,
            ageMinFormat,
            ageMinIsValid,
            ageMinRef,
            dropDownRef,
            resetFilter,
            sendAgeFilter,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';
.filter {
    display: flex;
    align-items: center;
    padding: 0 $padding-sm;
    border-right: 1px solid $lightest-grey;

    label {
        color: $light-grey;
    }

    .age-filters {
        display: flex;
        padding: 1rem;
        flex-direction: column;

        form {
            width: 100%;
        }
    }

    .input-group {
        flex-wrap: nowrap;

        .form-control.invalid {
            border-color: $bco-red;
        }

        label {
            font-weight: 500;
        }

        > * {
            margin: 0.25rem;
            width: 100%;
        }
    }
}
</style>

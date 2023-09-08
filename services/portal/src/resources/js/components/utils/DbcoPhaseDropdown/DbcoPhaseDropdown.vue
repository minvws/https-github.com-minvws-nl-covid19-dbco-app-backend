<template>
    <BDropdown
        id="dbco-phase-dropdown"
        variant="link"
        :text="phase ? phase.label : 'Fase'"
        :toggle-class="['text-decoration-none', toggleClass, 'text-left']"
        :disabled="!userCanEdit"
    >
        <BDropdownItem
            v-for="option in phaseOptions"
            :class="{ 'has-prepend-icon': phase && option.value === phase.value }"
            :key="option.value"
            @click="changePhase($as.any(option.value))"
        >
            <i v-if="phase && option.value === phase.value" class="icon icon--checkmark icon--center ml-0" />
            <span>{{ option.label }}</span>
        </BDropdownItem>
    </BDropdown>
</template>

<script lang="ts">
import { caseApi } from '@dbco/portal-api';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { userCanEdit } from '@/utils/interfaceState';
import type { BcoPhaseV1 } from '@dbco/enum';
import { bcoPhaseV1Options } from '@dbco/enum';

export default defineComponent({
    name: 'DbcoPhaseDropdown',
    props: {
        cases: {
            required: true,
            type: Array as PropType<string[]>,
        },
        bcoPhase: {
            required: false,
            type: String as PropType<`${BcoPhaseV1}`>,
        },
        toggleClass: {
            default: 'text-primary',
            required: false,
            type: String,
        },
    },
    data() {
        return {
            currentPhase: null as `${BcoPhaseV1}` | null,
        };
    },
    created() {
        if (this.bcoPhase) this.currentPhase = this.bcoPhase;
    },
    computed: {
        phase() {
            return this.currentPhase
                ? {
                      label: bcoPhaseV1Options[this.currentPhase],
                      value: this.currentPhase,
                  }
                : null;
        },
        phaseOptions() {
            return Object.entries(bcoPhaseV1Options)
                .sort(([phaseA], [phaseB]) => (phaseA < phaseB ? -1 : 1))
                .map(([phase, label]) => ({
                    label,
                    value: phase,
                }));
        },
        userCanEdit,
    },
    methods: {
        async changePhase(phase: BcoPhaseV1) {
            await caseApi.updateBCOPhase(phase, this.cases);
            this.currentPhase = phase;
            this.$emit('phaseChanged');
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';
#dbco-phase-dropdown {
    ::v-deep {
        .dropdown-menu {
            .dropdown-item {
                font-size: 0.875rem;
                padding: 0.875rem 0.75rem 0.875rem 2rem;
                cursor: pointer;
                max-width: 400px;
                color: $black;

                span {
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                &:hover {
                    background-color: $dropdown-link-hover-bg;
                }
            }
            .has-prepend-icon .dropdown-item {
                padding: 0.5rem;
            }
        }
    }
}
</style>

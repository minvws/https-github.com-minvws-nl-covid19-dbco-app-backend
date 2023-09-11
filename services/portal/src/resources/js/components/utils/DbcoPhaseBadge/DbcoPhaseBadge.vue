<template>
    <BBadge v-if="bcoPhase !== 'none'" variant="outline-light-grey">{{ bcoPhaseLabel }}</BBadge>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { BcoPhaseV1 } from '@dbco/enum';
import { bcoPhaseV1Options } from '@dbco/enum';

export default defineComponent({
    name: 'DbcoPhaseBadge',
    props: {
        bcoPhase: {
            type: String,
        },
        tooltipPlacement: {
            type: String,
            default: 'bottom',
        },
    },
    computed: {
        bcoPhaseLabel() {
            return this.bcoPhase ? bcoPhaseV1Options[this.bcoPhase as BcoPhaseV1] : '';
        },
        bcoPhaseTooltip() {
            if (!this.bcoPhase) return '';

            switch (this.bcoPhase) {
                case '1':
                    return 'Volledig bron- en contactonderzoek';
                case '2':
                    return 'Alleen contacten van indexen met prioriteit worden door de GGD nagebeld';
                case '3':
                    return 'Contacten van indexen worden niet door de GGD nagebeld';
                case '4':
                    return 'Alleen indexgesprek, contactinventarisatie door index zelf';
                case '5':
                    return 'Alleen uitslag doorbellen, CoronaMelder activeren en adviezen voor index & contacten doornemen en doorsturen';
                case 'steekproef':
                    return 'Steekproef';
                default:
                    return '';
            }
        },
    },
});
</script>

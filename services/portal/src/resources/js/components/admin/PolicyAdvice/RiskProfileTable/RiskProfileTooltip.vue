<!-- eslint-disable vuejs-accessibility/no-onchange, vuejs-accessibility/form-control-has-label -->
<template>
    <TooltipButton
        :data-testid="props.riskProfileEnum"
        size="md"
        class="tw-ml-2"
        position="right"
        icon="information-mark-circle"
    >
        {{ tooltipContent(props.riskProfileEnum) }}
        <ul class="tw-py-2 tw-pb-0 tw-px-6 tw-m-0">
            <li v-if="props.riskProfileEnum === 'hospital_admitted'">Ziekenhuisopname: Ja</li>
            <li v-if="props.riskProfileEnum === 'hospital_admitted'">
                Reden van ziekenhuisopname: alles behalve 'Andere indicatie' (dus: niet ingevuld, 'Onbekend' of
                'COVID-19')
            </li>
            <li v-if="props.riskProfileEnum === 'hospital_admitted'">
                Klachten: alles behalve 'Nee' (dus: niet ingevuld, 'Ja' of 'Onbekend')
            </li>

            <li v-if="props.riskProfileEnum === 'is_immuno_compromised' || props.riskProfileEnum === 'has_symptoms'">
                Klachten: Ja
            </li>
            <li v-if="props.riskProfileEnum === 'is_immuno_compromised'">Verminderde afweer: Ja</li>
            <li v-if="props.riskProfileEnum === 'no_symptoms'">Klachten: Nee</li>
        </ul>
    </TooltipButton>
</template>

<script lang="ts" setup>
import { TooltipButton } from '@dbco/ui-library';

const props = defineProps({
    riskProfileEnum: { type: String, required: true },
});

const tooltipContent = (profileEnum: string) => {
    switch (profileEnum) {
        case 'hospital_admitted':
            return 'Bij ziekenhuisopname moeten al deze punten waar zijn:';
        case 'is_immuno_compromised':
            return 'Bij verminderde afweer moeten al deze punten waar zijn:';
        case 'has_symptoms':
            return 'Symptomatische index standaard';
        case 'no_symptoms':
            return 'Asymptomatische index standaard';
    }
};
</script>

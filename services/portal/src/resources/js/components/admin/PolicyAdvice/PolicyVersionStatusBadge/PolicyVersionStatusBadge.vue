<template>
    <Badge v-if="isValidStatus" :color="badgeColor[status]">{{ policyVersionStatusV1Options[status] }}</Badge>
</template>

<script lang="ts" setup>
import { PolicyVersionStatusV1, policyVersionStatusV1Options } from '@dbco/enum';
import type { Extends, ThemeColor } from '@dbco/ui-library';
import { Badge } from '@dbco/ui-library';
import type { PropType } from 'vue';
import { computed } from 'vue';

type Color = Extends<ThemeColor, 'violet' | 'blue' | 'green' | 'gray'>;

const props = defineProps({
    status: { type: String as PropType<PolicyVersionStatusV1>, required: true },
});

const isValidStatus = computed(() => Object.keys(policyVersionStatusV1Options).includes(props.status));

const badgeColor: Record<PolicyVersionStatusV1, Color> = {
    [PolicyVersionStatusV1.VALUE_draft]: 'violet',
    [PolicyVersionStatusV1.VALUE_active_soon]: 'blue',
    [PolicyVersionStatusV1.VALUE_active]: 'green',
    [PolicyVersionStatusV1.VALUE_old]: 'gray',
};
</script>

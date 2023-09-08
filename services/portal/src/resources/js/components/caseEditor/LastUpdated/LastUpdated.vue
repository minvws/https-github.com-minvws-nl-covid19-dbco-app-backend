<template>
    <div class="last-updated" data-testid="last-updated">
        <div v-if="isUpdating">
            <BSpinner small class="mr-2" />
            <span class="d-none d-md-inline-block">Bezig met opslaan</span>
        </div>
        <div v-else-if="lastUpdated > mountedAt">
            <CheckIcon class="mr-2" />
            <span class="d-none d-md-inline-block"
                >Laatst opgeslagen - {{ $filters.dateFnsFormat(lastUpdated, 'HH:mm') }}</span
            >
        </div>
    </div>
</template>

<script lang="ts">
import { useAppStore } from '@/store/app/appStore';
import { defineComponent } from 'vue';
import { storeToRefs } from 'pinia';
import CheckIcon from '@icons/check.svg?vue';

export default defineComponent({
    name: 'LastUpdated',
    components: {
        CheckIcon,
    },
    setup() {
        const mountedAt = Date.now();
        const appStore = useAppStore();
        const { isUpdating, lastUpdated } = storeToRefs(appStore);

        return {
            mountedAt,
            isUpdating,
            lastUpdated,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.last-updated {
    color: $light-grey;
    font-size: 0.75rem;
    flex-shrink: 0;

    svg {
        height: 0.6875rem;
    }
}
</style>

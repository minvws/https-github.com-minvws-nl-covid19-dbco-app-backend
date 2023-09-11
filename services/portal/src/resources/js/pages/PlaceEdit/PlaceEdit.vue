<template>
    <div>
        <h1 class="sr-only">{{ t('pages.placeEdit.title') }}</h1>
        <div class="header-bar bg-white pt-4">
            <div class="w-100 px-3 px-xl-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap flex-md-nowrap gap-3">
                    <h2 class="d-flex flex-wrap text-nowrap mb-0 gap-x-3 gap-y-2" data-testid="place-label">
                        {{ place.label ? place.label : t('pages.placeEdit.title') }}
                    </h2>
                </div>
            </div>
        </div>

        <div class="tab-bar sticky-top tw-bg-white">
            <div class="w-100">
                <BTabs nav-class="nav-tabs--borderless px-3 px-xl-5" id="navtabs">
                    <BTab :title="t('pages.placeEdit.tabs.connectedIndex')" class="bg-gray">
                        <div class="container p-0 pt-5 px-3 px-xl-5">
                            <PlaceCasesTable :placeUuid="place.uuid" />
                        </div>
                    </BTab>
                    <BTab v-if="isPlaceVisitedTabEnabled" :title="t('pages.placeEdit.tabs.visited')" class="bg-gray">
                        <div class="container p-0 pt-5 px-3 px-xl-5">
                            <PlaceCasesVisitedTable :placeUuid="place.uuid" />
                        </div>
                    </BTab>
                </BTabs>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import type { PlaceDTO } from '@dbco/portal-api/place.dto';
import type { PropType } from 'vue';
import { defineComponent, computed } from 'vue';
import env from '@/env';
import { useI18n } from 'vue-i18n-composable';
import PlaceCasesTable from '@/components/contextManager/PlaceCasesTable/PlaceCasesTable.vue';
import PlaceCasesVisitedTable from '@/components/contextManager/PlaceCasesVisitedTable/PlaceCasesVisitedTable.vue';

export default defineComponent({
    name: 'PlaceEdit',
    props: {
        place: { type: Object as PropType<PlaceDTO>, required: true },
    },
    components: { PlaceCasesTable, PlaceCasesVisitedTable },
    setup() {
        const { t } = useI18n();
        const isPlaceVisitedTabEnabled = computed(() => env.isPlaceVisitedTabEnabled);
        return {
            t,
            isPlaceVisitedTabEnabled,
        };
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.bg-gray {
    background: $bco-grey;
}

.container {
    max-width: 100%;
}
</style>

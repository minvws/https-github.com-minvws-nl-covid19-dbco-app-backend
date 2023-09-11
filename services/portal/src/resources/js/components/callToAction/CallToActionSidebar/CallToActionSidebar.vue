<template>
    <ChoreSidebar :hint="hint" :title="tc(`components.callToActionSidebar.titles.${title}`)">
        <template #header>
            <button id="deselect" v-if="selectedCTA" type="button" @click="deselect">
                <i class="icon icon--close icon--lg" aria-hidden="true"></i>
                <span style="display: none">{{ tc('components.questionSidebar.close_question') }}</span>
            </button>
        </template>
        <template v-if="selectedCTA" #default>
            <FormInfo
                v-if="!pickedUp"
                class="info-block--lg"
                infoType="info"
                :text="tc(`components.callToActionSidebar.hints.pick_up`)"
            />
            <div id="body" :class="{ 'picked-up': pickedUp }">
                <CallToActionDetails :callToAction="selectedCTA" :pickedUp="pickedUp" />
                <CallToActionHistory :callToAction="$as.any(selectedCTA)" :pickedUp="pickedUp" />
            </div>
            <CallToActionForm :callToAction="selectedCTA" :pickedUp="pickedUp" />
        </template>
    </ChoreSidebar>
</template>

<script lang="ts">
import { computed, defineComponent, unref } from 'vue';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import CallToActionDetails from '@/components/callToAction/CallToActionDetails/CallToActionDetails.vue';
import CallToActionForm from '@/components/callToAction/CallToActionForm/CallToActionForm.vue';
import CallToActionHistory from '@/components/callToAction/CallToActionHistory/CallToActionHistory.vue';
import ChoreSidebar from '@/components/chore/ChoreSidebar/ChoreSidebar.vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { useI18n } from 'vue-i18n-composable';

export default defineComponent({
    components: {
        CallToActionDetails,
        CallToActionForm,
        CallToActionHistory,
        ChoreSidebar,
        FormInfo,
    },
    setup() {
        const deselect = () => {
            useCallToActionStore().selected = null;
        };

        const { tc } = useI18n();

        const selectedCTA = computed(() => useCallToActionStore().selected);

        const hint = computed(() =>
            unref(selectedCTA) ? undefined : tc(`components.callToActionSidebar.hints.no_selection`)
        );

        const pickedUp = computed(() => (unref(selectedCTA)?.assignedUserUuid ? true : false));

        const title = computed(() => (unref(selectedCTA) ? `selection` : `no_selection`));

        return {
            deselect,
            hint,
            pickedUp,
            selectedCTA,
            title,
            tc,
        };
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';
.info-block--lg {
    border-radius: 0;
}

#body {
    flex: 1 1 auto;
    overflow-y: auto;
}

#cta-details {
    background-color: $white;
    border-bottom: $border-default;
}
</style>

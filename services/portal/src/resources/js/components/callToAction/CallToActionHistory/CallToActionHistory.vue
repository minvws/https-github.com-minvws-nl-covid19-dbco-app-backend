<template>
    <article id="cta-history">
        <header>
            <h4>{{ $t(`components.callToActionSidebar.titles.history`) }}</h4>
        </header>
        <div v-if="pickedUp">
            <div v-if="loading">
                <p><BSpinner small /></p>
            </div>
            <div v-else>
                <div v-if="calltoActionHistoryItems && calltoActionHistoryItems.length" class="cta-history-item">
                    <h5>
                        {{ translatedActionWithUser(calltoActionHistoryItems[0]) }}
                    </h5>
                    <p v-if="calltoActionHistoryItems[0].note">
                        {{ calltoActionHistoryItems[0].note }}
                    </p>
                    <small>{{ calltoActionHistoryItems[0].datetime }}</small>
                </div>
                <BButton
                    v-if="!showMore && calltoActionHistoryItems && calltoActionHistoryItems.length > 1"
                    variant="link"
                    @click="showMore = true"
                    >{{ $t('components.callToActionSidebar.actions.show_history') }}
                    <i class="icon icon--chevron-down"></i
                ></BButton>
                <template v-for="(item, index) in calltoActionHistoryItems">
                    <div v-if="showMore && index !== 0" class="cta-history-item" :key="item.datetime + index">
                        <h5>
                            {{ translatedActionWithUser(item) }}
                        </h5>
                        <p v-if="item.note">{{ item.note }}</p>
                        <small>{{ item.datetime }}</small>
                    </div>
                </template>
            </div>
        </div>
        <div v-else>-</div>
    </article>
</template>

<script lang="ts">
import type { CallToActionHistoryItem, CallToActionResponse } from '@dbco/portal-api/callToAction.dto';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import type { PropType } from 'vue';
import { defineComponent, ref } from 'vue';
import type { TranslateResult } from 'vue-i18n';

export default defineComponent({
    props: {
        callToAction: { type: Object as PropType<CallToActionResponse>, required: true },
        pickedUp: { type: Boolean as PropType<boolean>, required: true },
    },
    setup() {
        const loading = ref<boolean>(true);
        let calltoActionHistoryItems = ref<CallToActionHistoryItem[]>([]);
        const showMore = ref<boolean>(false);

        const fetchHistory = async () => {
            loading.value = true;
            const historyResponse = await useCallToActionStore().getHistoryItems();
            calltoActionHistoryItems.value = historyResponse || [];
            loading.value = false;
        };
        void fetchHistory();

        return {
            fetchHistory,
            calltoActionHistoryItems,
            loading,
            showMore,
        };
    },
    watch: {
        async callToAction() {
            await this.fetchHistory();
            this.showMore = false;
        },
    },
    methods: {
        translatedActionWithUser(item: CallToActionHistoryItem): TranslateResult {
            const user = item.user;
            const translatedRoles = user.roles.map((role) => this.$t(`roles.${role}`)).join(', ');
            const nameAndRoles = [user.name, translatedRoles].filter(Boolean).join(', ');

            return this.$t(`components.callToActionSidebar.history.action.${item.callToActionEvent}`, { nameAndRoles });
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

#cta-history {
    padding: $padding-default;
    line-height: 1.25rem;

    div.cta-history-item:not(:last-child) {
        margin-bottom: $padding-sm;
    }
    div.cta-history-item:not(:first-child) {
        border-top: $border-default;
        padding-top: 1.5rem;
    }

    div.cta-history-item {
        display: flex;
        flex-direction: column;
        gap: $padding-xs;

        h5 {
            font-size: 0.875rem;
            font-weight: 700;
            margin: 0;
        }

        p {
            margin-bottom: 0;
            order: -1;
        }

        small {
            color: $dark-grey;
            font-size: 0.75rem;
        }
    }

    button {
        align-items: center;
        display: flex;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 0;

        i {
            height: 0.875em;
            margin-top: 2px;
            width: 0.875em;
        }
    }
}
</style>

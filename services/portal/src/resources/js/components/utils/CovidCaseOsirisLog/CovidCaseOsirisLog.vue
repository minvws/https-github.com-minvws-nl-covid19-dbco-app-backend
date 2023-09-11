<template>
    <div>
        <div v-if="isLoading" class="my-5">
            <p class="text-center">
                <BSpinner small />
            </p>
        </div>
        <div v-else-if="!isLoading && logItems.length === 0" class="my-5">
            <p>{{ $t('components.covidCaseOsirisLog.no_items') }}</p>
        </div>

        <div v-else>
            <h3 class="form-heading">
                {{ $t('components.covidCaseOsirisLog.titles.main', { logId: caseOsirisNumber ?? 'nog niet bekend' }) }}
            </h3>
            <Card v-for="item in logItems" :key="item.uuid" class="log-item-container">
                <div class="log-item">
                    <h4 class="note-title">{{ formattedTitle(item.status) }}</h4>
                    <p class="note-description">
                        {{
                            $t('components.covidCaseOsirisLog.number', {
                                osirisNumber: caseOsirisNumber?.toString() ?? 'nog niet bekend',
                            })
                        }}
                    </p>
                    <div v-if="item.osirisValidationResponse?.errors?.length" data-testid="validation-response">
                        <p class="note-description">
                            {{ $t('components.covidCaseOsirisLog.errors') }}
                        </p>
                        <ul>
                            <li v-for="error in item.osirisValidationResponse.errors">{{ error }}</li>
                        </ul>
                    </div>
                    <div v-if="item.osirisValidationResponse?.warnings?.length" data-testid="validation-response">
                        <p class="note-description">
                            {{ $t('components.covidCaseOsirisLog.warnings') }}
                        </p>
                        <ul>
                            <li v-for="warning in item.osirisValidationResponse.warnings">{{ warning }}</li>
                        </ul>
                    </div>
                    <div v-if="item.osirisValidationResponse?.messages?.length" data-testid="validation-response">
                        <p class="note-description">
                            {{ $t('components.covidCaseOsirisLog.messages') }}
                        </p>
                        <ul>
                            <li v-for="message in item.osirisValidationResponse.messages">{{ message }}</li>
                        </ul>
                    </div>
                    <p class="time-stamp">
                        {{ formatDate(parseDate(item.time), 'd MMMM yyyy HH:mm') + formattedDescription(item) }}
                    </p>
                </div>
            </Card>
        </div>
    </div>
</template>

<script lang="ts">
import useStatusAction, { isPending } from '@/store/useStatusAction';
import { caseApi } from '@dbco/portal-api';
import { computed, defineComponent, onMounted, ref } from 'vue';
import type { PropType } from 'vue';
import { useI18n } from 'vue-i18n-composable';
import type { OsirisLogItem } from '@dbco/portal-api/osiris.dto';
import { Card } from '@dbco/ui-library';
import type { TranslateResult } from 'vue-i18n';
import type { OsirisHistoryStatusV1 } from '@dbco/enum';
import { formatDate, parseDate } from '@/utils/date';

export default defineComponent({
    props: {
        caseUuid: { type: String as PropType<string>, required: true },
        caseOsirisNumber: { type: Number as PropType<number | null | undefined> },
    },
    components: {
        Card,
    },
    setup(props) {
        let logItems = ref<OsirisLogItem[]>([]);
        const { t } = useI18n();

        const { action: loadOsirisLog, status } = useStatusAction(async () => {
            const items = await caseApi.getOsirisLog(props.caseUuid);
            logItems.value = items;
        });

        const formattedDescription = (item: OsirisLogItem): string =>
            item.caseIsReopened ? ` - ${t('components.covidCaseOsirisLog.reopened')}` : '';

        const formattedTitle = (status: OsirisHistoryStatusV1): TranslateResult =>
            t(`components.covidCaseOsirisLog.titles.${status}`);

        onMounted(async () => {
            await loadOsirisLog();
        });

        const isLoading = computed(() => isPending(status.value));

        return {
            formatDate,
            formattedDescription,
            formattedTitle,
            isLoading,
            loadOsirisLog,
            logItems,
            parseDate,
            t,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.note-title {
    font-weight: 500;
    font-size: 16px;
    margin-bottom: 0.65rem;
}

.note-description {
    margin-bottom: $padding-xs;
}

.note-author {
    margin-bottom: 0.65rem;
}

.note-author {
    font-weight: 500;
}

.time-stamp {
    color: $dark-grey;
    margin-bottom: 0;
}
</style>

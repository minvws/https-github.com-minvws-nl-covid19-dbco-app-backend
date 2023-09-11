<template>
    <Container class="tw-py-12">
        <div class="tw-flex tw-flex-row tw-items-baseline tw-justify-between tw-mb-4">
            <Heading size="md">Cases</Heading>

            <HStack class="tw-items-center">
                <div class="tw-w-full tw-text-gray-500 tw-ml-4 text-right" data-testid="refreshed-at">
                    <span v-if="isRefreshing">Het verversen kan enkele minuten duren...</span>
                    <span v-else>Laatste berekening - {{ lastUpdatedString }}</span>
                </div>
                <i
                    v-if="!isRefreshing"
                    class="icon icon--questionmark tw-shrink-0 !tw-m-0"
                    v-b-tooltip.hover
                    title="De gegevens worden elke 30 minuten automatisch ververst"
                />
                <Button
                    size="sm"
                    iconLeft="arrow-round"
                    @click="handleRefreshClick"
                    :loading="isRefreshing"
                    loadingText="Bezig met verversen..."
                    data-testid="refreshButton"
                    >Nu verversen</Button
                >
            </HStack>
        </div>

        <TableContainer>
            <div v-if="isLoadingMetrics" class="tw-text-center tw-py-10" data-testid="loading">
                <BSpinner small />
            </div>

            <Table v-else-if="metrics.length">
                <Thead>
                    <Tr>
                        <Th scope="col">Datum</Th>
                        <Th scope="col" isNumeric>Toegevoegd</Th>
                        <Th scope="col" isNumeric>Gesloten</Th>
                    </Tr>
                </Thead>
                <Tbody>
                    <Tr v-for="metric in metrics" :key="metric.date">
                        <Td>{{ dateLast3Days(metric.date) || dateFormatMonthLong(metric.date) }}</Td>
                        <Td isNumeric>{{ metric.created }}</Td>
                        <Td isNumeric>{{ metric.archived }}</Td>
                    </Tr>
                </Tbody>
            </Table>

            <div v-else class="tw-text-center tw-py-10" data-testid="no-results">Geen resultaten gevonden.</div>
        </TableContainer>
    </Container>
</template>

<script lang="ts">
import {
    Heading,
    Table,
    TableContainer,
    Tbody,
    Td,
    Tfoot,
    Th,
    Thead,
    Tr,
    Container,
    HStack,
    Button,
    usePolling,
} from '@dbco/ui-library';
import { computed, defineComponent, onMounted, onUnmounted, ref } from 'vue';
import { caseMetricsApi } from '@dbco/portal-api';
import { useFilters } from '@/filters/useFilters';
import type { CasesCreatedArchivedMetric } from '@dbco/portal-api/caseMetrics.dto';
import { isToday } from 'date-fns';
import { useAppStore } from '@/store/app/appStore';
import type { AxiosRequestConfig } from 'axios';

export default defineComponent({
    components: {
        Heading,
        Table,
        TableContainer,
        Thead,
        Tbody,
        Tfoot,
        Th,
        Tr,
        Td,
        Container,
        HStack,
        Button,
    },
    setup() {
        const lastRefreshedAt = ref<DateStringISO8601 | null>(null);
        const metrics = ref<CasesCreatedArchivedMetric[]>([]);
        const isLoadingMetrics = ref(true);
        const isRequestingRefresh = ref(false);
        const appStore = useAppStore();
        const lastETag = ref<string | null>(null);

        const { startPolling, stopPolling, isPolling } = usePolling({
            request: (signal) => updateMetrics(false, { signal }),
            continuePolling: (modified) => !modified,
            timeout: 3 * 60 * 1000,
            onTimeout: () => {
                appStore.setHasError(true);
            },
        });

        const { dateLast3Days, dateFormatMonthLong, dateTimeFormatLong, dateFnsFormat } = useFilters();

        const updateMetrics = async (showLoadingState: boolean, config?: AxiosRequestConfig) => {
            isLoadingMetrics.value = showLoadingState;
            const res = await caseMetricsApi.getList(lastETag.value, config);
            const modified = res !== null;
            if (modified) {
                const { data, eTag, refreshedAt } = res;
                lastRefreshedAt.value = refreshedAt ? refreshedAt : null;
                lastETag.value = eTag;
                metrics.value = data;
            }
            isLoadingMetrics.value = false;
            return modified;
        };

        const isRefreshing = computed(() => isRequestingRefresh.value || isPolling.value);
        const lastUpdatedString = computed(() => {
            if (!lastRefreshedAt.value) {
                return '-';
            }

            const refreshDate = new Date(lastRefreshedAt.value);
            if (isToday(refreshDate)) {
                return dateFnsFormat(refreshDate, `H:mm`);
            }
            const last3Days = dateLast3Days(refreshDate);
            return last3Days ? last3Days + dateFnsFormat(refreshDate, ` 'om' H:mm`) : dateTimeFormatLong(refreshDate);
        });

        const handleRefreshClick = async () => {
            isRequestingRefresh.value = true;
            await caseMetricsApi.refresh();
            isRequestingRefresh.value = false;
            startPolling();
        };

        onMounted(() => {
            void updateMetrics(true);
        });

        onUnmounted(() => {
            stopPolling();
        });

        return {
            metrics,
            lastUpdatedString,
            isLoadingMetrics,
            dateLast3Days,
            dateFormatMonthLong,
            handleRefreshClick,
            isRefreshing,
        };
    },
});
</script>

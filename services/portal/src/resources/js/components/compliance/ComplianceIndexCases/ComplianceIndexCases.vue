<template>
    <div>
        <h4>Indexdossiers</h4>
        <table class="table table-rounded table-hover table-ggd table--clickable">
            <colgroup>
                <col class="w-20" />
                <col class="w-30" />
                <col class="td-icon" />
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Eerste ziektedag</th>
                    <th scope="col">Index</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="indexCase in indexCases" :key="indexCase.uuid" @click="$emit('navigate', indexCase.uuid)">
                    <td class="font-weight-bold">{{ $filters.dateFormat(indexCase.dateOfSymptomOnset) }}</td>
                    <td class="d-flex justify-content-between">
                        <span class="font-weight-bold"> {{ indexCase.number }}</span>
                    </td>
                    <td class="td-chevron">
                        <BButton variant="link" class="border-0" @click="$emit('navigate', indexCase.uuid)">
                            <ChevronRightIcon />
                        </BButton>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { IndexSearchResultDTO } from '@dbco/portal-api/case.dto';
import ChevronRightIcon from '@icons/chevron-right.svg?vue';

export default defineComponent({
    name: 'ComplianceIndexCases',
    components: { ChevronRightIcon },
    props: {
        indexCases: {
            type: Array as PropType<IndexSearchResultDTO[]>,
            required: true,
        },
    },
});
</script>

<style lang="scss" scoped>
tr td a {
    color: inherit;
}

.icon--chevron-right {
    margin: 0;
    height: 1.25rem;
    width: 1.25rem;
    padding-left: 0.75rem;
}
</style>

<template>
    <div>
        <h4>Contactdossiers</h4>
        <table class="table table-rounded table-hover table-ggd">
            <colgroup>
                <col class="w-20" />
                <col class="w-30" />
                <col class="w-30" />
                <col class="td-icon" />
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Contact datum</th>
                    <th scope="col">Categorie</th>
                    <th scope="col">Index</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="contactCase in contactCases" :key="contactCase.uuid">
                    <td class="font-weight-bold">{{ $filters.dateFormat(contactCase.contactDate) }}</td>
                    <td>{{ $filters.categoryFormat(contactCase.category) }}</td>
                    <td class="d-flex justify-content-between">
                        <span>
                            <span class="font-weight-bold">{{ contactCase.index.number }}</span>
                            <span v-if="relationShipLabel(contactCase)">({{ relationShipLabel(contactCase) }})</span>
                        </span>
                    </td>
                    <td class="td-chevron">
                        <BButton variant="link" class="border-0" @click="$emit('navigate', contactCase.uuid)">
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
import { relationshipV1Options } from '@dbco/enum';
import type { TaskSearchResultDTO } from '@dbco/portal-api/case.dto';
import ChevronRightIcon from '@icons/chevron-right.svg?vue';

type RelationshipV1OptionsKey = keyof typeof relationshipV1Options;

export default defineComponent({
    name: 'ComplianceContactCases',
    components: { ChevronRightIcon },
    data() {
        return {
            relationships: relationshipV1Options,
        };
    },
    props: {
        contactCases: {
            type: Array as PropType<TaskSearchResultDTO[]>,
            required: true,
        },
    },
    methods: {
        relationShipLabel(contactCase: TaskSearchResultDTO) {
            return this.relationships[contactCase.index.relationship as RelationshipV1OptionsKey];
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

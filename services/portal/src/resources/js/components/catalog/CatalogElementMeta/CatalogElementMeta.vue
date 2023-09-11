<template>
    <div>
        <div class="subtitle mono-font">{{ $as.any(element).type }}</div>
        <div class="d-flex justify-content-between">
            <div class="d-flex justify-content-start">
                <h1>
                    {{ element.name }} <BBadge variant="primary" class="ml-2">V {{ element.version }}</BBadge>
                </h1>
                <span v-if="element.diffToVersion" class="ml-2"
                    >vs<BBadge variant="light-grey" class="ml-2">V {{ element.diffToVersion }}</BBadge></span
                >
            </div>
            <div v-if="element.maxVersion.version > 1">
                <BDropdown
                    v-if="element.diffableVersions.length"
                    size="md"
                    right
                    text="Vergelijk met"
                    variant="outline"
                >
                    <BDropdownItem
                        v-for="version in element.diffableVersions"
                        :key="version.version"
                        @click="$emit('diffToVersion', version.version)"
                        >Versie {{ version.version }}</BDropdownItem
                    >
                </BDropdown>
                <BDropdown size="md" text="Andere versies" variant="outline-primary">
                    <BDropdownItem
                        v-for="(_, index) in new Array(element.maxVersion.version)"
                        :key="index"
                        @click="$emit('selectVersion', index + 1)"
                        >Versie {{ index + 1 }}</BDropdownItem
                    >
                </BDropdown>
            </div>
        </div>
        {{ element.label }}
        <div v-if="element.description || element.shortDescription">
            <hr />
            <h2>Beschrijving</h2>
            {{ element.description || element.shortDescription }}
        </div>
    </div>
</template>

<script lang="ts">
import type { CatalogDetailResponse } from '@dbco/portal-api/catalog.dto';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'CatalogElementMeta',
    props: {
        element: {
            type: Object as PropType<CatalogDetailResponse>,
            required: true,
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.subtitle {
    color: $lighter-grey;
    font-weight: 500;
}

h1::v-deep {
    .badge {
        vertical-align: top;
    }
}
</style>

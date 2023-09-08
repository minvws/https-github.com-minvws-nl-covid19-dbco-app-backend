<template>
    <LayoutWrapper :layout="layout">
        <HStack spacing="4">
            <div
                v-for="(element, index) in layout.uischema.elements"
                :key="`${layout.path}-${index}`"
                class="tw-flex-1"
            >
                <DispatchRenderer
                    :schema="layout.schema"
                    :uischema="element"
                    :path="layout.path"
                    :enabled="layout.enabled"
                    :renderers="layout.renderers"
                    :cells="layout.cells"
                />
            </div>
        </HStack>
    </LayoutWrapper>
</template>

<script lang="ts">
import { rendererProps, DispatchRenderer } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { useJsonFormsLayout } from '../../composition';
import LayoutWrapper from '../LayoutWrapper/LayoutWrapper.vue';
import { HStack } from '../../../components';
import type { Layout } from '@jsonforms/core';

export default defineComponent({
    components: { DispatchRenderer, LayoutWrapper, HStack },
    props: {
        ...rendererProps<Layout>(),
    },
    setup(props) {
        const { layout } = useJsonFormsLayout(props);

        return {
            layout,
        };
    },
});
</script>

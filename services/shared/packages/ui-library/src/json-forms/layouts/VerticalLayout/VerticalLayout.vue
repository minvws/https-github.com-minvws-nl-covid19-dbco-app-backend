<template>
    <LayoutWrapper :layout="layout">
        <VStack spacing="6">
            <div v-for="(element, index) in layout.uischema.elements" :key="`${layout.path}-${index}`">
                <DispatchRenderer
                    :schema="layout.schema"
                    :uischema="element"
                    :path="layout.path"
                    :enabled="layout.enabled"
                    :renderers="layout.renderers"
                    :cells="layout.cells"
                />
            </div>
        </VStack>
    </LayoutWrapper>
</template>

<script lang="ts">
import { rendererProps, DispatchRenderer } from '@jsonforms/vue2';
import { defineComponent } from 'vue';
import { useJsonFormsLayout } from '../../composition';
import LayoutWrapper from '../LayoutWrapper/LayoutWrapper.vue';
import { VStack } from '../../../components';
import type { Layout } from '@jsonforms/core';

export default defineComponent({
    components: { DispatchRenderer, LayoutWrapper, VStack },
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

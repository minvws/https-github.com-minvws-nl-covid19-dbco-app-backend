<template>
    <LayoutWrapper :layout="layout">
        <Alert :variant="uiOptions.variant">
            {{ label }}
            <template v-if="description" #additional>
                {{ description }}
            </template>
        </Alert>
    </LayoutWrapper>
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { computed, defineComponent } from 'vue';
import { useJsonFormsLayout, useUiOptions } from '../../composition';
import LayoutWrapper from '../LayoutWrapper/LayoutWrapper.vue';
import { Alert } from '../../../components';
import type { AlertElement } from '../../types';
import type {} from '@dbco/portal-open-api';
import type { I18nKey } from '../../core/JsonFormsBase/provide';
import { injectTranslation } from '../../core/JsonFormsBase/provide';

export default defineComponent({
    name: 'Message',
    components: {
        LayoutWrapper,
        Alert,
    },
    props: {
        ...rendererProps<AlertElement>(),
    },
    setup(props) {
        const { layout } = useJsonFormsLayout(props);
        const { t } = injectTranslation();

        const label = computed(() => {
            const { label } = layout.value;
            const { i18n } = layout.value.uischema;
            return i18n ? t(`${i18n}.label` as I18nKey) : label;
        });

        const description = computed(() => {
            const { description, i18n } = layout.value.uischema;
            return i18n ? t(`${i18n}.description` as I18nKey) : description;
        });

        return {
            label,
            description,
            layout,
            uiOptions: useUiOptions(layout),
        };
    },
});
</script>

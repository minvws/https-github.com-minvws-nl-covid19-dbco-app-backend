<template>
    <Button :disabled="!formHref" variant="outline" @click="onClick">{{ control.label }}</Button>
</template>

<script lang="ts">
import { rendererProps } from '@jsonforms/vue2';
import { computed, defineComponent } from 'vue';
import { Button } from '../../../components';
import type { ControlElement } from '../../types';
import { useJsonFormsControl } from '../../composition';
import { injectEventBus } from '../../core/JsonFormsBase/provide';

export default defineComponent({
    components: {
        Button,
    },
    props: {
        ...rendererProps<ControlElement>(),
    },
    setup(props) {
        const { eventBus } = injectEventBus();
        const { control } = useJsonFormsControl<string, void>(props);

        const formHref = computed(() => {
            // `path` could be undefined if it's invalid, but `data` might also default to the root data.
            // So we check both values
            if (!control.value.path || typeof control.value.data === 'string') {
                return;
            }
            return control.value.data;
        });

        const onClick = () => {
            if (!formHref.value) {
                console.error('Scope or data of the FormLink is invalid');
                return;
            }
            eventBus.$emit('formLink', { href: formHref.value });
        };

        return {
            formHref,
            control,
            onClick,
        };
    },
});
</script>

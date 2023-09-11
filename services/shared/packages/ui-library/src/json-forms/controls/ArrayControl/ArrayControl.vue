<template>
    <ControlWrapper :control="control" as="fieldset" class="tw-reset-fieldset">
        <ControlLabel :control="control" as="legend" class="tw-mb-2" />
        <ControlErrors :control="control" class="tw-mb-4" />

        <VStack v-if="hasData">
            <ArrayItem
                v-for="(_, index) in control.data"
                class="tw-flex-grow"
                data-testid="array-control-item"
                :key="`${control.path}-${index}`"
                :index="index"
                :showSortButtons="uiOptions.showSortButtons"
                :moveUpEnabled="moveUpEnabled && index > 0"
                :moveDownEnabled="moveDownEnabled && !!control.data && index < control.data.length - 1"
                :deleteEnabled="deleteEnabled"
                @delete="handleDeleteItem"
                @moveDown="handleMoveDown"
                @moveUp="handleMoveUp"
            >
                <DispatchRenderer
                    :schema="control.schema"
                    :uischema="childUiSchema"
                    :path="composePaths(control.path, `${index}`)"
                    :enabled="control.enabled"
                    :renderers="control.renderers"
                    :cells="control.cells"
                />
            </ArrayItem>
        </VStack>
        <div v-else>{{ uiOptions.noDataLabel || 'no data' }}</div>

        <Button @click="addDefaultItem" iconLeft="plus" class="tw-mt-4">{{ uiOptions.addLabel || 'Voeg toe' }}</Button>
    </ControlWrapper>
</template>

<script lang="ts">
import { composePaths, createDefaultValue } from '@jsonforms/core';
import { DispatchRenderer, rendererProps } from '@jsonforms/vue2';
import { computed, defineComponent } from 'vue';
import { Button, FormLabel, VStack } from '../../../components';
import type { ControlElement } from '../../types';
import { useChildUiSchema, useJsonFormsArrayControl, useUiOptions } from '../../composition';
import ControlErrors from '../ControlErrors/ControlErrors.vue';
import ControlLabel from '../ControlLabel/ControlLabel.vue';
import ControlWrapper from '../ControlWrapper/ControlWrapper.vue';
import ArrayItem from './ArrayItem.vue';

export default defineComponent({
    components: {
        ArrayItem,
        DispatchRenderer,
        Button,
        FormLabel,
        VStack,
        ControlErrors,
        ControlLabel,
        ControlWrapper,
    },
    props: {
        ...rendererProps<ControlElement<'array'>>(),
    },
    setup(props) {
        const { control, addItem, removeItems, moveDown, moveUp } = useJsonFormsArrayControl<unknown[], 'array'>(props);
        const hasData = computed(() => !!control.value.data && control.value.data?.length);
        const deleteEnabled = !!removeItems;
        const moveDownEnabled = !!moveDown;
        const moveUpEnabled = !!moveUp;

        function addDefaultItem() {
            addItem(control.value.path, createDefaultValue(control.value.schema))();
        }

        function handleDeleteItem(index: number) {
            if (removeItems) removeItems(control.value.path, [index])();
        }

        function handleMoveUp(index: number) {
            if (moveUp) moveUp(control.value.path, index)();
        }

        function handleMoveDown(index: number) {
            if (moveDown) moveDown(control.value.path, index)();
        }

        return {
            uiOptions: useUiOptions(control),
            childUiSchema: useChildUiSchema(control),
            addDefaultItem,
            composePaths,
            control,
            hasData,
            handleDeleteItem,
            handleMoveUp,
            handleMoveDown,
            deleteEnabled,
            moveDownEnabled,
            moveUpEnabled,
        };
    },
});
</script>

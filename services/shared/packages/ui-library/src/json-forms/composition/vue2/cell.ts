import type { ControlProps, RendererProps } from '@jsonforms/vue2';
import {
    useJsonFormsCell as useJsonFormsCellVue2,
    useJsonFormsEnumCell as useJsonFormsEnumCellVue2,
    useJsonFormsOneOfEnumCell as useJsonFormsOneOfEnumCellVue2,
} from '@jsonforms/vue2';

import type { Ref, UnwrapRef } from 'vue';
import type { ReplaceProp } from '../../../types/helpers';
import type { ControlElement, JsonFormsCell, CellBindings, UiSchemaOption } from '../../types';

/* c8 ignore start */
/* These methods only change the TypeScript types, they don't contain any actual implementation that needs testing. */

type CellCompositionFunction = (props: ControlProps) => {
    cell: Ref<JsonFormsCell>;
};

// This is a helper to add a more restrictive / detailed type for the cell data and ui options
type FixCellType<CompositionFunction extends CellCompositionFunction, DataType, Option extends UiSchemaOption> = (
    props: RendererProps<ControlElement<Option>>
) => ReplaceProp<
    ReturnType<CompositionFunction>,
    'cell',
    Ref<CellBindings<UnwrapRef<ReturnType<CompositionFunction>['cell']>, DataType, Option>>
>;

export const useJsonFormsCell = <T, O extends UiSchemaOption>(props: RendererProps<ControlElement<O>>) =>
    (useJsonFormsCellVue2 as unknown as FixCellType<typeof useJsonFormsCellVue2, T, O>)(props);

export const useJsonFormsEnumCell = <T, O extends UiSchemaOption>(props: RendererProps<ControlElement<O>>) =>
    (useJsonFormsEnumCellVue2 as unknown as FixCellType<typeof useJsonFormsEnumCellVue2, T, O>)(props);

export const useJsonFormsOneOfEnumCell = <T, O extends UiSchemaOption>(props: RendererProps<ControlElement<O>>) =>
    (useJsonFormsOneOfEnumCellVue2 as unknown as FixCellType<typeof useJsonFormsOneOfEnumCellVue2, T, O>)(props);

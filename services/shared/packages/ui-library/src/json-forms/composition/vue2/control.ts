import type { ControlProps, RendererProps } from '@jsonforms/vue2';
import {
    useJsonFormsControl as useJsonFormsControlVue2,
    useJsonFormsArrayControl as useJsonFormsArrayControlVue2,
    useJsonFormsEnumControl as useJsonFormsEnumControlVue2,
    useJsonFormsOneOfEnumControl as useJsonFormsOneOfEnumControlVue2,
} from '@jsonforms/vue2';
import type { Ref, UnwrapRef } from 'vue';
import type { ReplaceProp } from '../../../types/helpers';
import type { ControlElement, JsonFormsControl, ControlBindings, UiSchemaOption } from '../../types';

/* c8 ignore start */
/* These methods only change the TypeScript types, they don't contain any actual implementation that needs testing. */

type ControlCompositionFunction = (props: ControlProps) => {
    control: Ref<JsonFormsControl>;
};

// This is a helper to add a more restrictive / detailed type for the control data and ui options
type FixControlType<CompositionFunction extends ControlCompositionFunction, DataType, Option extends UiSchemaOption> = (
    props: RendererProps<ControlElement<Option>>
) => ReplaceProp<
    ReturnType<CompositionFunction>,
    'control',
    Ref<ControlBindings<UnwrapRef<ReturnType<CompositionFunction>['control']>, DataType, Option>>
>;

export const useJsonFormsControl = <T, O extends UiSchemaOption>(props: RendererProps<ControlElement<O>>) =>
    (useJsonFormsControlVue2 as unknown as FixControlType<typeof useJsonFormsControlVue2, T, O>)(props);

export const useJsonFormsArrayControl = <T, O extends UiSchemaOption>(props: RendererProps<ControlElement<O>>) =>
    (useJsonFormsArrayControlVue2 as unknown as FixControlType<typeof useJsonFormsArrayControlVue2, T, O>)(props);

export const useJsonFormsEnumControl = <T, O extends UiSchemaOption>(props: RendererProps<ControlElement<O>>) =>
    (useJsonFormsEnumControlVue2 as unknown as FixControlType<typeof useJsonFormsEnumControlVue2, T, O>)(props);

export const useJsonFormsOneOfEnumControl = <T, O extends UiSchemaOption>(props: RendererProps<ControlElement<O>>) =>
    (useJsonFormsOneOfEnumControlVue2 as unknown as FixControlType<typeof useJsonFormsOneOfEnumControlVue2, T, O>)(
        props
    );

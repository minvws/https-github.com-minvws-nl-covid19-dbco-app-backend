import type { LayoutProps, RendererProps } from '@jsonforms/vue2';
import { useJsonFormsLayout as useJsonFormsLayoutVue2 } from '@jsonforms/vue2';
import type { Ref, UnwrapRef } from 'vue';
import type { ReplaceProp } from '../../../types/helpers';
import type { AlertElement, JsonFormsLayout } from '../../types';
import type { Layout } from '@jsonforms/core';

/* c8 ignore start */
/* These methods only change the TypeScript types, they don't contain any actual implementation that needs testing. */

type LayoutCompositionFunction = (props: LayoutProps) => {
    layout: Ref<JsonFormsLayout>;
};

// This is a helper to add a more restrictive / detailed type for the layout
type FixLayoutType<CompositionFunction extends LayoutCompositionFunction, T extends Layout | AlertElement> = (
    props: RendererProps<Layout | AlertElement>
) => ReplaceProp<
    ReturnType<CompositionFunction>,
    'layout',
    Ref<ReplaceProp<UnwrapRef<ReturnType<CompositionFunction>['layout']>, 'uischema', T>>
>;

export const useJsonFormsLayout = <T extends Layout | AlertElement>(props: RendererProps<T>) =>
    (useJsonFormsLayoutVue2 as unknown as FixLayoutType<typeof useJsonFormsLayoutVue2, T>)(props);

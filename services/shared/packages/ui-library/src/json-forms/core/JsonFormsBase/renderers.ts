import type { JsonFormsRendererRegistryEntry } from '@jsonforms/core';
import {
    and,
    isBooleanControl,
    isCategorization,
    isEnumControl,
    isIntegerControl,
    isLayout,
    isMultiLineControl,
    isNumberControl,
    isOneOfEnumControl,
    isStringControl,
    or,
    rankWith,
    schemaTypeIs,
    uiTypeIs,
} from '@jsonforms/core';
import { hasRadioFormatOption, isCustomRenderer, isNonObjectType } from '../../utils';
import {
    ArrayControl,
    BooleanControl,
    ChildFormCollectionControl,
    ChildFormControl,
    FormLinkControl,
    InputControl,
    RadioEnumControl,
    RadioOneOfEnumControl,
} from '../../controls';
import { Categorization, HorizontalLayout, Alert, VerticalLayout } from '../../layouts';

const layouts: JsonFormsRendererRegistryEntry[] = [
    {
        renderer: Categorization,
        tester: rankWith(2, isCategorization),
    },
    {
        renderer: Alert,
        tester: rankWith(2, uiTypeIs('Alert')),
    },
    {
        renderer: HorizontalLayout,
        tester: rankWith(2, and(isLayout, uiTypeIs('HorizontalLayout'))),
    },
    {
        renderer: VerticalLayout,
        tester: rankWith(2, and(isLayout, uiTypeIs('VerticalLayout'))),
    },
];

const controls: JsonFormsRendererRegistryEntry[] = [
    {
        renderer: InputControl,
        tester: rankWith(2, or(isStringControl, isMultiLineControl, isNumberControl, isIntegerControl)),
    },
    {
        renderer: BooleanControl,
        tester: rankWith(2, isBooleanControl),
    },
    {
        renderer: ArrayControl,
        tester: rankWith(3, schemaTypeIs('array')),
    },
    {
        renderer: RadioEnumControl,
        tester: rankWith(3, and(isEnumControl, isNonObjectType, hasRadioFormatOption)),
    },
    {
        renderer: RadioOneOfEnumControl,
        tester: rankWith(3, and(isOneOfEnumControl, isNonObjectType, hasRadioFormatOption)),
    },
    {
        renderer: ChildFormControl,
        tester: rankWith(4, isCustomRenderer('ChildForm')),
    },
    {
        renderer: ChildFormCollectionControl,
        tester: rankWith(4, isCustomRenderer('ChildFormCollection')),
    },
    {
        renderer: FormLinkControl,
        tester: rankWith(4, isCustomRenderer('FormLink')),
    },
];

export const renderers: JsonFormsRendererRegistryEntry[] = [...layouts, ...controls];

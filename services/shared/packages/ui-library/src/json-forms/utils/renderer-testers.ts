import type { CustomControlElement, CustomControlElementCustomRenderer, EnumOptions } from '@dbco/portal-open-api';
import type { Tester } from '@jsonforms/core';
import { isIntegerControl, optionIs, isBooleanControl, isNumberControl, isStringControl, or } from '@jsonforms/core';

export const enumOptionIs = <K extends keyof EnumOptions>(key: K, value: EnumOptions[K]) =>
    optionIs(key as string, value);

export const isCustomRenderer = (id: CustomControlElementCustomRenderer) =>
    ((uiSchema) => (uiSchema as CustomControlElement).customRenderer === id) as Tester;

export const isNonObjectType = or(isStringControl, isBooleanControl, isNumberControl, isIntegerControl);

export const hasRadioFormatOption = or(enumOptionIs('format', 'radio'), enumOptionIs('format', 'radio-button'));

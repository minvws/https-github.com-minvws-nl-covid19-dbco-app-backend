import type { UiSchemaElement } from '@dbco/portal-open-api';
import type { ControlElement as ControlElementCore } from '@jsonforms/core';
import type {
    useJsonFormsArrayControl,
    useJsonFormsCell,
    useJsonFormsControl,
    useJsonFormsEnumCell,
    useJsonFormsEnumControl,
    useJsonFormsLayout,
    useJsonFormsOneOfEnumCell,
} from '@jsonforms/vue2';
import type { ReplaceProp } from '../../types/helpers';
import type { ControlElement, UiSchemaOption } from './ui-schema';
import type { UnwrapRef } from 'vue';

/**
 * Added the type here as the following reference was causing issues:
 *  type JsonFormsErrorObject = NonNullable<JsonFormsCore['errors']>[number];
 * @see: https://github.com/microsoft/TypeScript/issues/42873
 */
export interface FormError<K extends string = string, P = Record<string, any>, S = unknown> {
    keyword: K;
    instancePath: string;
    schemaPath: string;
    params: P;
    propertyName?: string;
    message?: string;
    schema?: S;
    parentSchema?: any;
    data?: unknown;
}

export type JsonFormsChangeHandler =
    | ReturnType<typeof useJsonFormsCell>['handleChange']
    | ReturnType<typeof useJsonFormsControl>['handleChange'];

export type JsonFormsCell = UnwrapRef<ReturnType<typeof useJsonFormsCell>['cell']>;
export type JsonFormsEnumCell = UnwrapRef<ReturnType<typeof useJsonFormsEnumCell>['cell']>;
export type JsonFormsOneOfEnumCell = UnwrapRef<ReturnType<typeof useJsonFormsOneOfEnumCell>['cell']>;

export type JsonFormsControl = UnwrapRef<ReturnType<typeof useJsonFormsControl>['control']>;
export type JsonFormsEnumControl = UnwrapRef<ReturnType<typeof useJsonFormsEnumControl>['control']>;
export type JsonFormsArrayControl = UnwrapRef<ReturnType<typeof useJsonFormsArrayControl>['control']>;
export type JsonFormsLayout = UnwrapRef<ReturnType<typeof useJsonFormsLayout>['layout']>;

export type CellBindings<
    Cell extends JsonFormsCell = JsonFormsCell,
    DataType = unknown,
    Options extends UiSchemaOption = unknown,
> = Omit<Cell, 'data' | 'uischema'> & {
    data: DataType | undefined;
    uischema: ControlElement<Options>;
};

export type ControlBindings<
    Control extends JsonFormsControl = JsonFormsControl,
    DataType = unknown,
    Options extends UiSchemaOption = unknown,
> = Omit<Control, 'data' | 'uischema'> & {
    data: DataType | undefined;
    uischema: ControlElement<Options>;
};

export type LayoutBindings = ReplaceProp<JsonFormsLayout, 'uischema', UiSchemaElement>;

export type ArrayControlBindings = ControlBindings<JsonFormsArrayControl, any, any>;

/**
 * Because we use a more restrict type for the UI schema / control element
 * we sometimes need to cast it back to the original type because they are not compatible.
 * Therefore we export the original type here.
 */
export type { ControlElementCore };

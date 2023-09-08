import type {
    BooleanOptions,
    CommonTypeOptions,
    StringOptions,
    EnumOptions,
    ControlElement as OpenApiControlElement,
    ArrayOptions,
    CustomControlElementCustomRenderer,
    ChildFormControlElementCustomRenderer,
    ChildFormControlElementOptions,
    UiSchema as OpenApiUiSchema,
    AlertElementOptions,
} from '@dbco/portal-open-api';
import type { DeepReadonly } from '../../types/helpers';

export type { AlertElement, ChildFormControlElement } from '@dbco/portal-open-api';

export type UiSchemaOptions = {
    array: ArrayOptions;
    string: StringOptions;
    boolean: BooleanOptions;
    number: CommonTypeOptions;
    integer: CommonTypeOptions;
    datetime: CommonTypeOptions;
    date: CommonTypeOptions;
    time: CommonTypeOptions;
    enum: EnumOptions;
    alert: AlertElementOptions;
    ['child-form']: ChildFormControlElementOptions;
};

export type UiSchemaOption = keyof UiSchemaOptions | unknown;

export interface ControlElement<Option extends UiSchemaOption | unknown = unknown> extends OpenApiControlElement {
    options: Option extends keyof UiSchemaOptions ? UiSchemaOptions[Option] : never;
}

export type CustomRendererType = CustomControlElementCustomRenderer | ChildFormControlElementCustomRenderer;

export type UiSchema = OpenApiUiSchema | DeepReadonly<OpenApiUiSchema>;

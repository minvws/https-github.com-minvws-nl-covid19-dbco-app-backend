import type { JsonSchemaDraft07, SimpleTypes as JsonSchemaType } from '@dbco/portal-open-api';
import type { DeepReadonly } from 'vue';

export type { JsonSchemaType };

export type JsonSchema = JsonSchemaDraft07 | DeepReadonly<JsonSchemaDraft07>;

type ArrayJsonSchema<T> = {
    type: 'array';
    items: T;
};

type ObjectJsonSchema<T> = {
    type: 'object';
    properties: T;
} & Pick<JsonSchema, 'required'>;

export type ChildFormJsonSchema<T extends GenericObject = any> = ObjectJsonSchema<{
    items: ArrayJsonSchema<ObjectJsonSchema<T>>;
}>;

export type ChildFormCollectionJsonSchema<T extends GenericObject = any> = ObjectJsonSchema<{
    items: ArrayJsonSchema<ObjectJsonSchema<T>>;
}>;

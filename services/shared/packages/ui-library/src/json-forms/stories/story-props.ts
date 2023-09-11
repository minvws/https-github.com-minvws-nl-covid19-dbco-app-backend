import type { Component } from 'vue';
import type { JsonFormsStoryProps } from '../core/JsonForms/json-forms-story-props';

export { JsonFormsStoryProps };

export type JsonFormsControlStoryProps<T extends GenericObject = GenericObject> = JsonFormsStoryProps<T>;

export interface JsonFormsCellStoryProps extends JsonFormsStoryProps {
    cell: Component;
}

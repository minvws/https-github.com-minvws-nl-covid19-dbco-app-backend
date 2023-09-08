import type { JsonSchema, UiSchema } from '../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data = {
    value: 'Alias blanditiis',
};

const schema: JsonSchema = {
    type: 'object',
    properties: {
        value: {
            type: 'string',
        },
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Alert',
            label: 'Elements can be shown or hidden with rules',
            description: 'Type "foo" or "bar" to see the alert below',
            options: {
                variant: 'info',
            },
        },
        {
            type: 'Control',
            scope: '#/properties/value',
        },
        {
            type: 'Alert',
            label: 'Foo or bar',
            rule: {
                effect: 'SHOW',
                condition: {
                    scope: '#/properties/value',
                    schema: { enum: ['foo', 'bar'] },
                },
            },
            options: {
                variant: 'success',
            },
        },
    ],
};

export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
    useActionHandler: false,
};

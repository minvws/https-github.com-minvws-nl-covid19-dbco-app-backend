import type { JsonSchema, UiSchema } from '../../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data = {
    example: {
        one: 'Placeat',
        two: 'Quod',
        three: 'Saepe',
    },
};

const schema: JsonSchema = {
    type: 'object',
    properties: {
        example: {
            type: 'object',
            properties: {
                one: { type: 'string' },
                two: {
                    type: 'string',
                    i18n: 'example.schema',
                },
                three: {
                    type: 'string',
                },
            },
        },
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            scope: '#/properties/example/properties/one',
            i18n: 'example.uiSchema',
        },
        {
            type: 'Control',
            scope: '#/properties/example/properties/two',
        },
        {
            type: 'Control',
            scope: '#/properties/example/properties/three',
        },
    ],
};
export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
    useActionHandler: false,
    i18nResource: {
        example: {
            schema: {
                label: 'Een label d.m.v. i18n key in het schema',
            },
            uiSchema: {
                label: 'Een label d.m.v. i18n key in het uiSchema',
            },
            three: {
                label: 'Een label op basis van het pad',
            },
        },
    },
};

import type { JsonSchema, UiSchema } from '../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data = {
    boolean: true,
    date: '2020-01-01',
    dateTime: '2000-01-01T15:55:35',
    time: '15:55:35',
    enum: 'one',
    enumOneOf: 'foo',
    integer: 2,
    number: 2.56,
    text: 'Alias facere pariatur in dolorem impedit assumenda quidem eius iusto provident a saepe aperiam.',
    textArea: 'Alias facere pariatur in dolorem\nimpedit assumenda quidem eius iusto\nprovident a saepe aperiam.',
};

const schema: JsonSchema = {
    type: 'object',
    properties: {
        boolean: { type: 'boolean' },
        date: { type: 'string', format: 'date' },
        dateTime: { type: 'string', format: 'date-time' },
        time: { type: 'string', format: 'time' },
        enum: { type: 'string', enum: ['one', 'two', 'three'] },
        enumOneOf: {
            type: 'string',
            oneOf: [{ const: 'foo' }, { const: 'bar' }, { const: 'baz' }],
        },
        integer: { type: 'integer' },
        number: { type: 'number' },
        text: { type: 'string' },
        textArea: { type: 'string' },
        radio: { type: 'string', enum: ['one', 'two', 'three'] },
        radioButton: { type: 'string', enum: ['one', 'two', 'three'] },
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: Object.keys(schema.properties!).map((key) => {
        let options: GenericObject | undefined;

        switch (key) {
            case 'textArea':
                options = { multi: true };
                break;
            case 'radio':
                options = { format: 'radio' };
                break;
            case 'radioButton':
                options = { format: 'radio-button' };
                break;
        }

        return {
            type: 'Control',
            scope: `#/properties/${key}`,
            options,
        };
    }),
};

export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
    useActionHandler: false,
};

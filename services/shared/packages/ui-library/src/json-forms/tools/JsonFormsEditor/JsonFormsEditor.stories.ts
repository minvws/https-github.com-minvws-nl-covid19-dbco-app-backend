import type { Meta, StoryFn } from '@storybook/vue';
import { ref } from 'vue';
import JsonFormsEditor from './JsonFormsEditor.vue';
import type { JsonFormsEditorChangeEvent } from './types';

const data = {
    name: 'John',
    likesChocolate: false,
};

const schema = {
    type: 'object',
    required: ['name', 'likesChocolate'],
    properties: {
        name: {
            type: 'string',
            minLength: 3,
        },
        likesChocolate: {
            type: 'boolean',
        },
    },
};

const uiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'HorizontalLayout',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/name',
                },
                {
                    type: 'Control',
                    scope: '#/properties/likesChocolate',
                },
            ],
        },
    ],
};

const additionalErrors = [
    {
        instancePath: '/name',
        message: 'New error',
        schemaPath: '',
        keyword: '',
        params: {},
    },
];

const story: Meta = {
    title: 'JsonForms/Tools/JsonFormsEditor',
    component: JsonFormsEditor,
    parameters: {
        docs: {
            description: {
                component: 'Multiple JSON editors for editing JSON forms schemas.',
            },
        },
    },
    args: {
        data,
        schema,
        uiSchema,
        additionalErrors,
    },
    argTypes: {
        data: { control: { type: 'object' } },
        schema: { control: { type: 'object' } },
        uiSchema: { control: { type: 'object' } },
        additionalErrors: { control: { type: 'array' } },
    },
};

export const Default: StoryFn = ({
    data: initialData,
    schema: initialSchema,
    uiSchema: initialUiSchema,
    additionalErrors: initialAdditionalErrors,
}) => ({
    components: { JsonFormsEditor },
    setup() {
        const data = ref(initialData);
        const schema = ref(initialSchema);
        const uiSchema = ref(initialUiSchema);
        const additionalErrors = ref(initialAdditionalErrors);

        const handleChange = ({
            schema: newSchema,
            data: newData,
            uiSchema: newUiSchema,
            additionalErrors: newAdditionalErrors,
        }: JsonFormsEditorChangeEvent) => {
            if (newSchema) schema.value = newSchema;
            if (newData) data.value = newData;
            if (newUiSchema) uiSchema.value = newUiSchema;
            if (additionalErrors) additionalErrors.value = newAdditionalErrors;
        };

        return { data, schema, uiSchema, additionalErrors, handleChange };
    },
    template: `
    <div>
        <JsonFormsEditor :data="data" :schema="schema" :uiSchema="uiSchema" :additionalErrors="additionalErrors" @change="handleChange"/>
        <code><pre>{{JSON.stringify({schema, uiSchema, data, additionalErrors}, null, 2)}}</pre></code>
    </div>
`,
});

Default.parameters = {
    docs: {
        description: {
            story: '',
        },
    },
};

export default story;

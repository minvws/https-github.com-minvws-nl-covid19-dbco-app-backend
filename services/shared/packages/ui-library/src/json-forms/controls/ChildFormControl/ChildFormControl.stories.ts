import type { Meta } from '@storybook/vue';
import { JsonFormsControlStory } from '../../stories';
import type { JsonSchema, FormData, UiSchema, ChildFormControlElement } from '../../types';
import { setupStory } from '../../../../docs/utils/story';

export default {
    title: 'JsonForms/Controls/ChildFormControl',
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?node-id=262%3A686',
        },
        docs: {
            description: {
                component: `
The \`ChildFormControl\` renders a \`JsonFormsChild\` within the main form. It is defined by the \`$links\` meta data. And will use said meta data to perform the necessary HTTP requests.
Note how in the example below different requests are used for the root and the child form data.
`,
            },
        },
    },
} as Meta;

const data: FormData = {
    rootValue: 'Autem illo aliquid',
    $links: {
        update: { href: '/api/root-form' },
    },
    child: {
        childValue: 'Repellendus eum ipsa',
        $links: {
            update: { href: '/api/child-form', method: 'POST' },
        },
    } as FormData,
};

const schema: JsonSchema = {
    type: 'object',
    required: ['root', 'child'],
    properties: {
        rootValue: {
            type: 'string',
        },
        child: {
            type: 'object',
            properties: {
                childValue: { type: 'string' },
            },
        },
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            scope: '#/properties/rootValue',
        },
        {
            type: 'Control',
            scope: '#/properties/child',
            customRenderer: 'ChildForm',
            options: {
                detail: {
                    type: 'Control',
                    scope: '#/properties/childValue',
                },
            },
        } as ChildFormControlElement,
    ],
};

export const Default = setupStory({
    components: { JsonFormsControlStory },
    props: {
        schema,
        uiSchema,
        data,
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

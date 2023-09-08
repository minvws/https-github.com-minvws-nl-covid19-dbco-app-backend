import type { Meta } from '@storybook/vue';
import type {
    JsonSchema,
    UiSchema,
    ChildFormCollectionJsonSchema,
    FormCollectionData,
    FormData,
    ChildFormControlElement,
} from '../../types';
import { setupStory } from '../../../../docs/utils/story';
import { JsonFormsControlStory } from '../../stories';

export default { title: 'JsonForms/Controls/ChildFormCollectionControl' } as Meta;

const data: FormData = {
    general: {
        company: 'VWS',
    },
    employees: {
        items: [
            {
                firstName: 'Jane',
                lastName: 'Doe',
                $links: {
                    self: { href: '/api/employees/1' },
                },
            },
            {
                firstName: 'John',
                lastName: 'Dane',
                $links: {
                    self: { href: '/api/employees/2' },
                },
            },
        ],
        $links: {
            get: { href: '/api/employees' },
            create: { href: '/api/employees', method: 'POST' },
        },
    } as FormCollectionData,
    $links: {
        self: { href: '/api/company/1' },
    },
};

const schema: JsonSchema = {
    type: 'object',
    required: ['general', 'employees'],
    properties: {
        general: {
            type: 'object',
            properties: {
                company: {
                    type: 'string',
                },
            },
        },
        employees: {
            type: 'object',
            properties: {
                items: {
                    type: 'array',
                    items: {
                        type: 'object',
                        required: ['firstName', 'lastName'],
                        properties: {
                            firstName: { type: 'string' },
                            lastName: { type: 'string' },
                        },
                    },
                },
            },
        } as ChildFormCollectionJsonSchema,
    },
};

const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            scope: '#/properties/general/properties/company',
        },
        {
            type: 'Control',
            scope: '#/properties/employees',
            customRenderer: 'ChildFormCollection',
            options: {
                detail: {
                    type: 'HorizontalLayout',
                    elements: [
                        {
                            type: 'Control',
                            scope: '#/properties/firstName',
                        },
                        {
                            type: 'Control',
                            scope: '#/properties/lastName',
                        },
                    ],
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

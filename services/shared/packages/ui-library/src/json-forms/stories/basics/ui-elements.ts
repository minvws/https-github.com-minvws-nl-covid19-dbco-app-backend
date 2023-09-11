import type { JsonSchema, UiSchema } from '../../../types';
import type { JsonFormsStoryProps } from '../../core/JsonForms/json-forms-story-props';

const data = {};
const schema: JsonSchema = {};
const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Alert',
            label: 'Alert element',
            description: 'Asperiores mollitia eum nam distinctio adipisci quae ipsam.',
        },
    ],
};

export const props: JsonFormsStoryProps = {
    data,
    schema,
    uiSchema,
    useActionHandler: false,
};

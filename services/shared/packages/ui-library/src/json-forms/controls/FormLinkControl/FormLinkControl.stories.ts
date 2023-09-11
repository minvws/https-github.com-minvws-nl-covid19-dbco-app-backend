import { action } from '@storybook/addon-actions';
import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import { JsonFormsControlStory } from '../../stories';
import type { FormData, JsonSchema, UiSchema } from '../../types';

const onFormLink = action('formLink');

const data: FormData = {
    $links: {},
    $forms: {
        example: 'https://example.com/form/1',
    },
};

const schema: JsonSchema = {
    type: 'object',
    properties: {},
};

const uiSchema: UiSchema = {
    type: 'Control',
    customRenderer: 'FormLink',
    label: 'Open form',
    scope: '#/properties/$forms/properties/example',
};

export const Default = setupStory({
    components: { JsonFormsControlStory },
    props: {
        data,
        schema,
        uiSchema,
        onFormLink,
    },
    template: `<JsonFormsControlStory :data="data" :schema="schema" :uiSchema="uiSchema" @formLink="onFormLink" />`,
});

export default { title: 'JsonForms/Controls/FormLinkControl' } as Meta;

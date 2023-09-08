import type { Meta } from '@storybook/vue';
import { setupStory } from '../../../../docs/utils/story';
import type { JsonFormsStoryProps } from '../../stories';
import { JsonFormsControlStory } from '../../stories';

const data = {};
const schema = {};

export const Default = setupStory<JsonFormsStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data,
        schema,
        uiSchema: {
            type: 'Alert',
            label: 'Itaque ratione praesentium corrupti magnam vel nemo aperiam ullam.',
            description:
                'Asperiores mollitia eum nam distinctio adipisci quae ipsam. Officiis delectus repellendus dolorum molestiae ratione omnis eligendi mollitia odit maiores ea provident modi tenetur.',
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export const i18n = setupStory<JsonFormsStoryProps>({
    components: { JsonFormsControlStory },
    props: {
        data,
        schema,
        uiSchema: {
            type: 'Alert',
            i18n: 'ipsam',
            options: {
                variant: 'warning',
            },
        },
        i18nResource: {
            ipsam: {
                label: 'Quia voluptatem voluptatem voluptatem.',
                description: 'Illum officiis perspiciatis consequuntur quae suscipit soluta recusandae fuga quos.',
            },
        },
    },
    template: `<JsonFormsControlStory v-bind="props" />`,
});

export default { title: 'JsonForms/Layouts/Alert' } as Meta;

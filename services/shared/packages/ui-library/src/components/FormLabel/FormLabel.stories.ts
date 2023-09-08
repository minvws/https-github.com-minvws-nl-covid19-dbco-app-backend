import type { Meta } from '@storybook/vue';
import FormLabel from './FormLabel.vue';
import { TooltipButton } from '../..';
import { setupStory } from '../../../docs/utils/story';

const asValues = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'div', 'label'] as const;

export default {
    title: 'Components/FormLabel',
    component: FormLabel,
    parameters: {
        design: {
            type: 'figma',
            url: 'https://www.figma.com/file/iAImi4EP2J7SWH1Vs1sX9N/%F0%9F%8E%A8-Components?node-id=368-848&t=HqBC3W5IUJOguECJ-0',
        },
    },
    args: {
        content: 'Repellat aperiam laudantium vel eligendi assumenda.',
    },
    argTypes: {
        content: {
            control: 'text',
        },
        as: { options: asValues, control: { type: 'select' } },
    },
} as Meta;

export const Default = setupStory({
    components: { FormLabel },
    template: `
    <FormLabel :as="args.as">
        {{ args.content }}
    </FormLabel>`,
});

export const WithExtraContent = setupStory({
    components: { FormLabel },
    template: `
    <FormLabel :as="args.as">
        {{ args.content }}
        Accusamus quam odit voluptatem magnam id odit omnis maxime quos rerum.
        <template #extra>
            Illo exercitationem sunt ab omnis
        </template>
    </FormLabel>`,
});

export const WithTooltipButton = setupStory({
    components: { FormLabel, TooltipButton },
    template: `
    <FormLabel :as="args.as">
        {{ args.content }}
        <template #extra>
            (Veritatis magni illum)
            <TooltipButton class="tw-ml-2">
                Pariatur impedit illum facere beatae ipsam.
            </TooltipButton>
        </template>
    </FormLabel>`,
});

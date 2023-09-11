import type { StoryConfig } from '../../../docs/utils/story';
import { setupStory } from '../../../docs/utils/story';
import type { JsonFormsStoryProps } from './story-props';
import { default as JsonFormsStory } from '../core/JsonForms/JsonFormsStory.vue';

export interface JsonFormsStoryConfig extends Omit<StoryConfig<JsonFormsStoryProps>, 'template'> {
    template?: string;
}

export const setupJsonFormsStory = ({ props, template, ...rest }: JsonFormsStoryConfig) =>
    setupStory<JsonFormsStoryProps>({
        components: { JsonFormsStory },
        props,
        template: template || `<JsonFormsStory v-bind="props" />`,
        ...rest,
    });

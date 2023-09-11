import type { Meta } from '@storybook/vue';
import { setupJsonFormsStory } from '../setup-json-forms-story';
import { props as basicControls } from './controls';
import { props as basicUiElements } from './ui-elements';

export default {
    title: 'JsonForms/Basics',
    parameters: {
        docs: {
            description: {
                component: 'JsonForms basic elements',
            },
        },
    },
} as Meta;

export const Controls = setupJsonFormsStory({ props: basicControls });

export const UiElements = setupJsonFormsStory({ props: basicUiElements });

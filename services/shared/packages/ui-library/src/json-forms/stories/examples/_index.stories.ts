import type { Meta } from '@storybook/vue';
import { setupJsonFormsStory } from '../setup-json-forms-story';
import { props as basicForm } from './basic-form';
import { props as childForm } from './child-form';
import { props as childFormCollection } from './child-form-collection';
import { props as chocolates } from './chocolates';
import { props as rules } from './rules';

export default {
    title: 'JsonForms/Examples',
    parameters: {
        docs: {
            description: {
                component: 'JsonForms examples',
            },
        },
    },
} as Meta;

export const BasicForm = setupJsonFormsStory({ props: basicForm });

export const ChildForm = setupJsonFormsStory({ props: childForm });

export const ChildFormCollection = setupJsonFormsStory({ props: childFormCollection });

export const Chocolates = setupJsonFormsStory({ props: chocolates });

export const Rules = setupJsonFormsStory({ props: rules });

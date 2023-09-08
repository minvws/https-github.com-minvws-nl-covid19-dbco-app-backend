import type { Meta } from '@storybook/vue';
import FixedTypeJsonEditor from './FixedTypeJsonEditor.vue';
import { setupStory } from '../../../../docs/utils/story';

const example = {
    array: [1, 2, 3],
    boolean: true,
    color: '#82b92c',
    null: null,
    number: 123,
    object: { a: 'b', c: 'd' },
    string: 'Hello World',
};

export default {
    title: 'JsonForms/Tools/FixedTypeJsonEditor',
    component: FixedTypeJsonEditor,
    parameters: {
        docs: {
            description: {
                component:
                    'A JSON editor that will only allow the content type to match the type of the original value.',
            },
        },
    },
} as Meta;

export const Object = setupStory({
    components: { FixedTypeJsonEditor },
    props: { value: example },
    template: `<FixedTypeJsonEditor :value="value" />`,
});

export const Array = setupStory({
    components: { FixedTypeJsonEditor },
    props: { value: [{ a: 'b' }, { c: 'd' }] },
    template: `<FixedTypeJsonEditor :value="value" />`,
});

export const String = setupStory({
    components: { FixedTypeJsonEditor },
    props: {
        value: 'Dolores eum debitis neque consectetur at cupiditate eligendi optio perspiciatis tenetur dolores animi dolorem provident.',
    },
    template: `<FixedTypeJsonEditor :value="value" />`,
});

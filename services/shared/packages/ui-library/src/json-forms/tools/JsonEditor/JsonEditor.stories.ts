import type { Meta, StoryFn } from '@storybook/vue';
import { action } from '@storybook/addon-actions';
import JsonEditor from './JsonEditor.vue';
import { safeParse } from './utils';

const onChange = action('change');

const example = {
    array: [1, 2, 3],
    boolean: true,
    color: '#82b92c',
    null: null,
    number: 123,
    object: { a: 'b', c: 'd' },
    string: 'Hello World',
};

const story: Meta = {
    title: 'JsonForms/Tools/JsonEditor',
    component: JsonEditor,
    parameters: {
        docs: {
            description: {
                component:
                    'A JSON editor that is built on top op [`json-editor-vue`](https://github.com/cloydlau/json-editor-vue). It accepts any value other than `undefined` and shows the edtiable JSON representation of said value.',
            },
        },
    },
    args: {
        stringifiedValue: JSON.stringify(example),
    },
    argTypes: {
        stringifiedValue: { control: { type: 'text' } },
    },
};

type StoryConfig = {
    description?: string;
    props?: {
        errorMessage?: string;
    };
};

function setupStory({ props = {}, description }: StoryConfig): StoryFn {
    const Story: StoryFn = ({ stringifiedValue }) => ({
        components: { JsonEditor },
        setup() {
            const { errorMessage } = props;
            const editorValue = safeParse(stringifiedValue) || stringifiedValue;
            return { ...props, value: editorValue, errorMessage, onChange };
        },
        template: `<JsonEditor :value="value" :errorMessage="errorMessage" @change="onChange"/>`,
    });

    Story.parameters = {
        docs: {
            description: {
                story: description,
            },
        },
    };

    return Story;
}

export const Default = setupStory({});
export const CustomErrorMessage = setupStory({
    props: {
        errorMessage:
            'Consequuntur incidunt eligendi asperiores architecto harum dicta ducimus quae earum iure quia debitis.',
    },
});

export default story;

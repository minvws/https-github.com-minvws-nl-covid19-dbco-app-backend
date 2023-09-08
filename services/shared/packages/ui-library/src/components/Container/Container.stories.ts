import type { Meta, StoryFn } from '@storybook/vue';
import Container from './Container.vue';
import { InfoLabel } from '../../../docs/components';

const asValues = ['div', 'span', 'section', 'article'] as const;
const sizeValues = ['sm', 'md', 'lg', 'xl'] as const;

interface Props {
    content: string;
    as: typeof asValues;
    size: typeof sizeValues;
    centeredContent: boolean;
}

const story: Meta = {
    title: 'Components/Container',
    component: Container,
    args: {
        content:
            'Laudantium dolores libero cum ea quae pariatur possimus quo adipisci sit corrupti. Quidem deleniti dolor quo esse facere adipisci assumenda sint praesentium eveniet repudiandae eveniet et iusto. Facere minus unde odit officiis non suscipit optio tenetur quas voluptate pariatur. Voluptate harum excepturi quos veniam excepturi aperiam alias exercitationem commodi aut atque facere quam. Natus rerum modi repellendus debitis cumque maiores in quisquam occaecati in perferendis.',
    },
    argTypes: {
        content: {
            control: 'text',
        },
        as: { options: asValues, control: { type: 'select' } },
        size: { options: sizeValues, control: { type: 'select' } },
        centeredContent: { control: 'boolean' },
    },
};

const Template: StoryFn<Props> = ({ content, ...args }) => {
    return {
        components: { Container },
        setup() {
            return { args, content };
        },
        template: `<Container v-bind="args">{{content}}</Container>`,
    };
};

const SizesTemplate: StoryFn<Props> = ({ content, ...args }) => {
    return {
        components: { Container, InfoLabel },
        setup() {
            return { args, content, sizeValues };
        },
        template: `
        <div>
            <div v-for="size in sizeValues" class='tw-mb-4'>
                <Container v-bind="args" :size='size'>{{content}}</Container>
                <InfoLabel class="tw-mt-2">
                    {{ size }}
                </InfoLabel>
            </div>
        </div>`,
    };
};

export const Default = Template.bind({});

export const Sizes = SizesTemplate.bind({});

export default story;

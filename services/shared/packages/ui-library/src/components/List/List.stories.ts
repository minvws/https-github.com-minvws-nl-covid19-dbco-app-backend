import type { Meta, StoryFn } from '@storybook/vue';
import UnorderedList from './UnorderedList.vue';
import ListItem from './ListItem.vue';

const story: Meta = {
    title: 'Components/List',
    component: UnorderedList,
    parameters: {},
};

type StoryConfig = {
    template: string;
    description?: string;
    props?: Record<string, unknown>;
};

function setupStory({ template, props = {}, description }: StoryConfig): StoryFn {
    const Story: StoryFn = ({ ...args }) => ({
        components: { UnorderedList, ListItem },
        setup() {
            return { args, ...props };
        },
        template,
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

export const Unordered = setupStory({
    template: `
    <div>
        <UnorderedList class="tw-mb-10">
            <ListItem>Debitis recusandae rem debitis reprehenderit quas expedita deserunt labore magni nemo illo at in quo.</ListItem>
            <ListItem>Aliquid commodi accusamus provident sunt esse sunt quaerat architecto reiciendis ad.</ListItem>
            <ListItem>Animi fugiat reprehenderit quo enim minus id recusandae non consectetur dicta.</ListItem>
            <ListItem>Repellendus hic earum temporibus delectus temporibus ipsa placeat nobis aut.</ListItem>
            <ListItem>Totam dolorum ducimus voluptates quis earum veniam aliquid reiciendis nulla laudantium atque vitae cumque et.</ListItem>
        </UnorderedList>

        <UnorderedList class="tw-body-lg">
            <ListItem>Debitis recusandae rem debitis reprehenderit quas expedita deserunt labore magni nemo illo at in quo.</ListItem>
            <ListItem>Aliquid commodi accusamus provident sunt esse sunt quaerat architecto reiciendis ad.</ListItem>
            <ListItem>Animi fugiat reprehenderit quo enim minus id recusandae non consectetur dicta.</ListItem>
            <ListItem>Repellendus hic earum temporibus delectus temporibus ipsa placeat nobis aut.</ListItem>
            <ListItem>Totam dolorum ducimus voluptates quis earum veniam aliquid reiciendis nulla laudantium atque vitae cumque et.</ListItem>
        </UnorderedList>
    </div>
   `,
});

export default story;

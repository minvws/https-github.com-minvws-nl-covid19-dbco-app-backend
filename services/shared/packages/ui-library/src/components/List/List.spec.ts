import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import { defineComponent } from 'vue';
import { UnorderedList, ListItem } from '.';

const List = defineComponent({
    components: { UnorderedList, ListItem },
    template: `
<UnorderedList>
    <ListItem>Debitis recusandae rem debitis reprehenderit quas expedita deserunt labore magni nemo illo at in quo.</ListItem>
    <ListItem>Aliquid commodi accusamus provident sunt esse sunt quaerat architecto reiciendis ad.</ListItem>
    <ListItem>Animi fugiat reprehenderit quo enim minus id recusandae non consectetur dicta.</ListItem>
    <ListItem>Repellendus hic earum temporibus delectus temporibus ipsa placeat nobis aut.</ListItem>
    <ListItem>Totam dolorum ducimus voluptates quis earum veniam aliquid reiciendis nulla laudantium atque vitae cumque et.</ListItem>
</UnorderedList>`,
});

function createComponent(Component: any) {
    return mount(Component, {
        localVue: createDefaultLocalVue(),
    });
}

describe('List', () => {
    it('List should render with all items', async () => {
        const wrapper = createComponent(List);
        expect(wrapper.findAllComponents(ListItem)).toHaveLength(5);
    });
});

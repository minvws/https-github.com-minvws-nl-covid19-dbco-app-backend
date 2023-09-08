// mocked `node_modules` module does not seem to work without this reset
// NOTE: This reset needs to be at the top of this file!
// @see https://github.com/vitest-dev/vitest/issues/1484#issuecomment-1155518865
vi.resetModules();

import { faker } from '@faker-js/faker';
import { mount } from '@vue/test-utils';
import { Link } from '..';
import { createDefaultLocalVue } from '../../test/local-vue';
import Collapse from './Collapse.vue';

vi.mock('animejs');

type Props = {
    initialIsOpen: boolean;
    labelOpen: string;
    labelClosed: string;
};

function createComponent(propsData: Props) {
    return mount(Collapse, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

const labelClosed = faker.lorem.word() + ' closed';
const labelOpen = faker.lorem.word() + ' open';

describe('Collapse.vue', () => {
    it('renders as initially closed', () => {
        const wrapper = createComponent({
            initialIsOpen: false,
            labelOpen,
            labelClosed,
        });
        expect(wrapper.text()).not.toContain(labelOpen);
        expect(wrapper.text()).toContain(labelClosed);
    });

    it('renders as initially open', () => {
        const wrapper = createComponent({
            initialIsOpen: true,
            labelOpen,
            labelClosed,
        });

        expect(wrapper.text()).toContain(labelOpen);
        expect(wrapper.text()).not.toContain(labelClosed);
    });

    it('changes state on click', async () => {
        const wrapper = createComponent({
            initialIsOpen: false,
            labelOpen,
            labelClosed,
        });

        expect(wrapper.text()).not.toContain(labelOpen);
        expect(wrapper.text()).toContain(labelClosed);

        await wrapper.findComponent(Link).trigger('click');

        expect(wrapper.text()).toContain(labelOpen);
        expect(wrapper.text()).not.toContain(labelClosed);
    });
});

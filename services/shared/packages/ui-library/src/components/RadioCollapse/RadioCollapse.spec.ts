// mocked `node_modules` module does not seem to work without this reset
// NOTE: This reset needs to be at the top of this file!
// @see https://github.com/vitest-dev/vitest/issues/1484#issuecomment-1155518865
vi.resetModules();

import { faker } from '@faker-js/faker';
import { shallowMount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import RadioCollapse from './RadioCollapse.vue';
import { Radio } from '../Radio';

vi.mock('animejs');

type Props = {
    title: string;
    initialIsOpen?: boolean;
    openButtonLabel?: string;
    closeButtonLabel?: string;
};

function createComponent(propsData: Props) {
    return shallowMount(RadioCollapse, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

describe('RadioCollapse.vue', () => {
    it('renders as initially closed', () => {
        const wrapper = createComponent({
            title: faker.lorem.sentence(),
        });

        expect(wrapper.find('radiogroup-stub').attributes('value')).toBe('false');
    });

    it('renders as initially open', () => {
        const wrapper = createComponent({
            initialIsOpen: true,
            title: faker.lorem.sentence(),
        });

        expect(wrapper.find('radiogroup-stub').attributes('value')).toBe('true');
    });

    it('changes state on click', async () => {
        const wrapper = createComponent({
            title: faker.lorem.sentence(),
        });

        const firstRadio = wrapper.findAllComponents(Radio).at(0);

        expect(wrapper.find('radiogroup-stub').attributes('value')).toBe('false');

        await firstRadio.vm.$emit('click');

        expect(wrapper.find('radiogroup-stub').attributes('value')).toBe('true');
    });
});

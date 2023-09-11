import Icon from './Icon.vue';
import { createDefaultLocalVue } from '../../test/local-vue';
import { mount } from '@vue/test-utils';
import type { IconName } from './icons';

type Props = {
    name: IconName;
};

function createComponent(propsData: Props) {
    return mount(Icon, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

describe('Icon.vue', () => {
    it('should be able to render an icon', () => {
        const wrapper = createComponent({ name: 'arrow-combined' });
        expect(wrapper.element.tagName).toBe('svg');
    });
});

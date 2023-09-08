import { createLocalVue, shallowMount } from '@vue/test-utils';
import BulkActionBar from './BulkActionBar.vue';
import { registerDirectives } from '@dbco/ui-library';

describe('BulkActionBar.vue', () => {
    const localVue = createLocalVue();

    registerDirectives(localVue);

    const getWrapper = () => {
        return shallowMount(BulkActionBar, {
            localVue,
            propsData: {
                text: 'title',
            },
            slots: {
                default: '<div data-testid="test-child">test</div>',
            },
        });
    };

    it('should load', () => {
        const wrapper = getWrapper();

        expect(wrapper.find('div').exists()).toBe(true);
    });

    it('should emit "hide" when close button is clicked', async () => {
        const wrapper = getWrapper();

        await wrapper.find('.bulk-actionbar__close').vm.$emit('click');

        expect(wrapper.emitted('hide')).toBeTruthy();
    });

    it('should show the text', () => {
        const wrapper = getWrapper();
        const textDiv = wrapper.find('.bulk-actionbar__text');

        expect(textDiv.text()).toBe('title');
    });

    it('should render default slot children', () => {
        const wrapper = getWrapper();

        expect(wrapper.find('[data-testid="test-child"]').exists()).toBe(true);
    });
});

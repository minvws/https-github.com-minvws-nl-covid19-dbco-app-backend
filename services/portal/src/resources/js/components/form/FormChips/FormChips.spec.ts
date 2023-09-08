import { createLocalVue, mount, shallowMount } from '@vue/test-utils';
import FormChips from './FormChips.vue';

import VueFormulate from '@braid/vue-formulate';

const context = {
    type: 'FormChips',
    name: 'test',
    attributes: {
        placeholder: 'placeholder',
    },
    isSubField: () => false,
    model: ['a', 'b', 'c'],
    options: [
        {
            value: 'a',
            label: 'A',
        },
        {
            value: 'b',
            label: 'B',
        },
        {
            value: 'c',
            label: 'C',
        },
    ],
};

describe('FormChips.vue', () => {
    const localVue = createLocalVue();
    localVue.directive('click-outside', vi.fn());
    localVue.use(VueFormulate);

    const getWrapper = (props?: object, data: object = {}, rootModel: object = {}, isShallowMount = true) => {
        const mountFn = isShallowMount ? shallowMount : mount;
        return mountFn(FormChips, {
            localVue,
            propsData: props,
            data: () => data,
            stubs: {},
            provide: {
                rootModel: () => rootModel,
            },
        });
    };

    it('should open when clicking on "chips" element', async () => {
        const props = { context };
        const data = { isOpen: false };
        const wrapper = getWrapper(props, data);

        const clickElement = wrapper.find('.chips');
        await clickElement.trigger('click');
        await wrapper.vm.$nextTick();

        expect(clickElement.element.classList.contains('open')).toBe(true);
    });

    it('should show "placeholder-container" if model.length is null or undefined', () => {
        const props = { context };
        const data = { isOpen: false };
        const wrapper = getWrapper(props, data);

        expect(wrapper.find('.placeholder.container').exists()).toBe(true);
    });

    it('should not have "placeholder-container" if model.length is defined', () => {
        const props = { context };
        const data = { isOpen: false };
        const rootModel = {
            test: ['a', 'b', 'c'],
        };
        const wrapper = getWrapper(props, data, rootModel);

        expect(wrapper.find('.placeholder.container').exists()).toBe(false);
    });

    it('should render chip button for every selected option', () => {
        const props = { context };
        const data = { isOpen: true };
        const rootModel = {
            test: ['a', 'b', 'c'],
        };

        const wrapper = getWrapper(props, data, rootModel);

        expect(wrapper.findAll('.chip').length).toBe(3);
    });

    it('should remove a chip when clicking on', async () => {
        const props = { context };
        const data = { isOpen: true };
        const rootModel = {
            test: ['a', 'b', 'c'],
        };

        const wrapper = getWrapper(props, data, rootModel);
        await wrapper.vm.$nextTick();

        // We can only monitor the context model value, since the buttons are rendered through rootModel
        expect(wrapper.vm.$props.context.model.length).toBe(3);

        wrapper.find('.chips formulateinput-stub').vm.$emit('click', { stopPropagation: vi.fn() }, 'a');
        expect(wrapper.vm.$props.context.model.length).toBe(2);
    });

    it('should display a list of FormulateInput elements if isOpen is true the elements should be of type "checkbox"', () => {
        const props = { context };
        const data = { isOpen: true };

        const wrapper = getWrapper(props, data);
        expect(wrapper.find('[data-testid="container-options"]').isVisible()).toBe(true);
        expect(wrapper.find('[data-testid="container-options"] .list').exists()).toBe(true);
    });

    it('should not display a list of FormulateInput elements if isOpen is false', () => {
        const props = { context };
        const data = { isOpen: false };

        const wrapper = getWrapper(props, data);
        expect(wrapper.find('[data-testid="container-options"]').isVisible()).toBe(false);
        expect(wrapper.find('[data-testid="container-options"] .list').exists()).toBe(false);
    });

    it('should show "Geen resultaten" if no search results are found', () => {
        const props = { context };
        const data = { isOpen: true, search: 'z' };

        const wrapper = getWrapper(props, data);
        expect(wrapper.find('[data-testid="container-options"]').text()).toBe('Geen resultaten');
    });

    it('should not show "Geen resultaten" if search results are found', () => {
        const props = { context };
        const data = { isOpen: true, search: 'a' };

        const wrapper = getWrapper(props, data);
        expect(wrapper.find('[data-testid="container-options"]').text()).not.toBe('Geen resultaten');
    });

    it('should  disable chips if disabled prop is true', () => {
        const props = { context, disabled: true };
        const data = {};
        const rootModel = {
            test: ['a', 'b', 'c'],
        };

        const wrapper = getWrapper(props, data, rootModel);

        expect(wrapper.find("[data-testid='chip']").attributes().disabled).toBe('true');
    });

    it('should not disable chips and show delete icon if disabled prop is false', () => {
        const props = { context, disabled: false };
        const data = {};
        const rootModel = {
            test: ['a', 'b', 'c'],
        };

        const wrapper = getWrapper(props, data, rootModel, false);
        const chip = wrapper.findAll("[data-testid='chip']").at(0);

        expect(chip.attributes().disabled).toBe(undefined);
        expect(chip.find('.icon--delete-circle').exists()).toBe(true);
    });
});

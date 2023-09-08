import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import RadioGroup from './FormRadioGroup.vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(RadioGroup, {
        localVue,
        propsData: props,
        stubs: {
            FormulateFormWrapper: true,
        },
    });
});

describe('RadioGroup.vue', () => {
    it('should display the title as a legend on the component', () => {
        // ARRANGE
        const props = {
            context: {
                model: 'testModel',
            },
            title: 'testTitle',
            expand: [],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('legend').text()).toBe(props.title);
    });

    it('should display styling if isOpen is true', () => {
        // ARRANGE
        const props = {
            context: {
                model: 'testModel',
            },
            title: 'testTitle',
            expand: ['testModel'],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('div[style="display: none;"]').exists()).toBe(false);
    });

    it('should not display styling if isOpen is false', () => {
        // ARRANGE
        const props = {
            context: {
                model: 'testModel',
            },
            title: 'testTitle',
            expand: [],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('div[style="display: none;"]').exists()).toBe(true);
    });

    it('should trigger the watcher when the values changes', async () => {
        // ARRANGE
        const props = {
            context: {
                model: 'testModel',
                options: 'testOptions',
            },
            title: 'testTitle',
            expand: [],
        };
        const spyWatcher = await vi.spyOn((RadioGroup as any).watch, 'context.model');
        await spyWatcher.mockReset();

        const wrapper = createComponent(props);

        // ACT
        await wrapper.setProps({ context: { model: 'testModel2' }, title: 'test' });

        // ASSERT
        await expect(spyWatcher).toBeCalledTimes(1);
    });

    it('should trigger onchange when onchange gets called on vueformulate', async () => {
        // ARRANGE
        const props = {
            context: {
                model: 'testModel',
                options: 'testOptions',
            },
            title: 'testTitle',
            expand: [],
        };
        const spyOnchange = await vi.spyOn((RadioGroup as any).methods, 'onChange');
        await spyOnchange.mockReset();

        const wrapper = createComponent(props);

        // ACT
        await wrapper.find('formulateformwrapper-stub').trigger('change');

        // ASSERT
        await expect(spyOnchange).toBeCalledTimes(1);
    });
});

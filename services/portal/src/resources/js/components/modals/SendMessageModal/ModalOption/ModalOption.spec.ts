import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import { BFormInput, BFormSelect, BFormSelectOption } from 'bootstrap-vue';
import type { VueConstructor } from 'vue';
import ModalOption from './ModalOption.vue';

const createComponent = setupTest((localVue: VueConstructor, props: object = {}, data: object = {}) =>
    shallowMount(ModalOption, {
        localVue,
        propsData: props,
        data: () => data,
        slots: {
            default: 'test',
        },
        stubs: {
            BFormInput: BFormInput,
            BFormSelect: BFormSelect,
            BFormSelectOption: BFormSelectOption,
        },
        attachTo: document.body,
    })
);

describe('ModalOption.vue', () => {
    it('should show a tooltip if prop "tooltip" is set', () => {
        const wrapper = createComponent({
            label: 'Test label',
            tooltip: 'Test tooltip',
        });

        expect(wrapper.find('[data-testid="tooltip"]').exists()).toBe(true);
    });

    it('should NOT show a tooltip if prop "tooltip" is not set', () => {
        const wrapper = createComponent({
            label: 'Test label',
        });

        expect(wrapper.find('[data-testid="tooltip"]').exists()).toBe(false);
    });

    it('should show a note if prop "note" is set', () => {
        const wrapper = createComponent({
            label: 'Test label',
            note: 'Test note',
        });

        expect(wrapper.find('.note').exists()).toBe(true);
    });

    it('should NOT show a note if prop "note" is not set', () => {
        const wrapper = createComponent({
            label: 'Test label',
        });

        expect(wrapper.find('.note').exists()).toBe(false);
    });

    it('should show an error if prop "error" is set', () => {
        const wrapper = createComponent({
            error: 'Test error',
            label: 'Test label',
        });

        expect(wrapper.find('[data-testid="error"]').exists()).toBe(true);
    });

    it('should NOT show an error if prop "error" is not set', () => {
        const wrapper = createComponent({
            label: 'Test label',
        });

        expect(wrapper.find('[data-testid="error"]').exists()).toBe(false);
    });

    describe('InputType.Text', () => {
        it('should show a BFormInput if prop "type" === InputType.Text', () => {
            const wrapper = createComponent({
                label: 'Test label',
                type: 'text',
            });

            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(true);
        });

        it('should show a BFormInput by default if prop "type" is not set', () => {
            const wrapper = createComponent({
                label: 'Test label',
            });

            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(true);
        });

        it('should show chip if value is not empty and no error/focus', () => {
            const wrapper = createComponent({
                label: 'Test label',
                value: 'abc',
            });

            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(false);
            expect(wrapper.find('.chip').exists()).toBe(true);
        });

        it('should render default slot in chip', () => {
            const wrapper = createComponent({
                label: 'Test label',
                value: 'abc',
            });

            expect(wrapper.find('.chip').text()).toBe('test');
        });

        it('should show field if error', () => {
            const wrapper = createComponent({
                label: 'Test label',
                error: 'Test error',
            });

            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(true);
            expect(wrapper.find('.chip').exists()).toBe(false);
        });

        it('should show field if focus', () => {
            const wrapper = createComponent(
                {
                    label: 'Test label',
                },
                {
                    isFocus: true,
                }
            );

            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(true);
            expect(wrapper.find('.chip').exists()).toBe(false);
        });

        it('should show field if no value', () => {
            const wrapper = createComponent({
                label: 'Test label',
            });

            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(true);
            expect(wrapper.find('.chip').exists()).toBe(false);
        });

        it('should show emit "focus" if clicked on chip (=focused)', async () => {
            const wrapper = createComponent({
                label: 'Test label',
                value: 'abc',
            });

            const chip = wrapper.find('.chip');
            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(false);
            expect(chip.exists()).toBe(true);
            await chip.trigger('click');

            expect(wrapper.emitted().focus?.[0]).toEqual([]);
        });

        it('should show field and focus if clicked on chip', async () => {
            const wrapper = createComponent({
                label: 'Test label',
                value: 'abc',
            });

            const chip = wrapper.find('.chip');
            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(false);
            expect(chip.exists()).toBe(true);
            await chip.trigger('click');

            const input = wrapper.findComponent({ name: 'BFormInput' });
            expect(input.exists()).toBe(true);
            // Check if input is focused
            expect(document.activeElement).toBe(input.element);

            expect(wrapper.find('.chip').exists()).toBe(false);
        });

        it('should blur field if pressed enter', async () => {
            const wrapper = createComponent({
                label: 'Test label',
            });

            const input = wrapper.findComponent({ name: 'BFormInput' });
            (input.element as HTMLInputElement).focus();
            // Focus on input
            expect(document.activeElement).toBe(input.element);

            await input.trigger('keydown.enter');

            // Focus not on input
            expect(document.activeElement).toBe(document.body);
        });

        it('should emit "change" event when changed', async () => {
            const wrapper = createComponent({
                label: 'Test label',
            });

            expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(true);
            expect(wrapper.find('.chip').exists()).toBe(false);

            const input = wrapper.findComponent({ name: 'BFormInput' });
            await input.setValue('abc');
            await input.trigger('blur');

            expect(wrapper.emitted().change?.[0]).toEqual(['abc']);
        });
    });

    describe('InputType.Select', () => {
        it('should show a BFormSelect if prop "type" === InputType.Select', () => {
            const wrapper = createComponent({
                label: 'Test label',
                type: 'select',
            });

            expect(wrapper.findComponent({ name: 'BFormSelect' }).exists()).toBe(true);
        });

        it('should emit "change" event when changed', async () => {
            const wrapper = createComponent({
                label: 'Test label',
                type: 'select',
                value: 'abc',
            });

            const select = wrapper.findComponent({ name: 'BFormSelect' });
            await select.vm.$emit('change');

            expect(wrapper.emitted().change?.[0]).toEqual(['abc']);
        });

        it('should have class "placeholder-active" if no value', () => {
            const wrapper = createComponent({
                label: 'Test label',
                type: 'select',
            });

            const select = wrapper.findComponent({ name: 'BFormSelect' });
            expect(select.classes()).toContain('placeholder-active');
        });

        it('should NOT have class "placeholder-active" if value', () => {
            const wrapper = createComponent({
                label: 'Test label',
                type: 'select',
                value: 'abc',
            });

            const select = wrapper.findComponent({ name: 'BFormSelect' });
            expect(select.classes()).not.toContain('placeholder-active');
        });

        it('should have placeholder option which is disabled and hidden if prop "placeholder" is set', () => {
            const wrapper = createComponent({
                label: 'Test label',
                type: 'select',
                placeholder: 'Test placeholder',
            });

            const select = wrapper.findComponent({ name: 'BFormSelect' });
            expect(select.find('option[disabled][hidden]').exists()).toBe(true);
        });

        it('should NOT have placeholder option if prop "placeholder" is not set', () => {
            const wrapper = createComponent({
                label: 'Test label',
                type: 'select',
            });

            const select = wrapper.findComponent({ name: 'BFormSelect' });
            expect(select.find('option[disabled][hidden]').exists()).toBe(false);
        });
    });
});

import { createLocalVue, mount, shallowMount } from '@vue/test-utils';
import MedicinePicker from './FormMedicinePicker.vue';
import BootstrapVue from 'bootstrap-vue';
import type { UntypedWrapper } from '@/utils/test';

describe('MedicinePicker.vue', () => {
    const localVue = createLocalVue();

    localVue.use(BootstrapVue);

    const getWrapper = (props?: object, data?: object) => {
        return shallowMount(MedicinePicker, {
            localVue,
            propsData: props,
            data: () => data,
        }) as UntypedWrapper;
    };

    const getMountedWrapper = (props?: object, data?: object) => {
        return mount(MedicinePicker, {
            localVue,
            propsData: props,
            data: () => data,
        }) as UntypedWrapper;
    };

    it('should add a new table row for each value in values plus an empty line with null', () => {
        // ARRANGE
        const props = {
            context: {
                model: [
                    {
                        name: 'aaaaa',
                        remark: 'bbbbb',
                        knownEffects: 'ccccc',
                    },
                    {
                        name: 'ddddd',
                        remark: 'eeeee',
                        knownEffects: 'fffff',
                    },
                ],
            },
        };

        const data = {};

        const wrapper = getWrapper(props, data);

        // ASSERT
        expect(wrapper.findAll('[data-testid="medicine-name"]').at(0).attributes('value')).toBe('aaaaa');
        expect(wrapper.findAll('[data-testid="medicine-remark"]').at(0).attributes('value')).toBe('bbbbb');
        expect(wrapper.findAll('[data-testid="medicine-known-effects"]').at(0).attributes('value')).toBe('ccccc');
        expect(wrapper.findAll('[data-testid="medicine-name"]').at(1).attributes('value')).toBe('ddddd');
        expect(wrapper.findAll('[data-testid="medicine-remark"]').at(1).attributes('value')).toBe('eeeee');
        expect(wrapper.findAll('[data-testid="medicine-known-effects"]').at(1).attributes('value')).toBe('fffff');
        expect(wrapper.findAll('[data-testid="medicine-name"]').at(2).attributes('value')).toBe('');
        expect(wrapper.findAll('[data-testid="medicine-remark"]').at(2).attributes('value')).toBe(undefined);
        expect(wrapper.findAll('[data-testid="medicine-known-effects"]').at(2).attributes('value')).toBe(undefined);
    });

    it('should add a new medicine, model.name, onBlur of the medicine-name input field if index is valid', async () => {
        // ARRANGE
        const props = {
            context: {
                model: [],
            },
        };

        const data = {
            values: [],
        };

        const wrapper = getMountedWrapper(props, data);

        // ACT
        const el = await wrapper.findAll('[data-testid="medicine-name"]').at(0);
        await el.setValue('zzzzz');
        await el.trigger('blur');

        // ASSERT
        await expect(wrapper.vm.values).toMatchObject([
            { knownEffects: null, name: 'zzzzz', remark: null },
            { knownEffects: null, name: '', remark: null },
        ]);
    });

    it('should not add a new medicine, model.name, onBlur of the medicine-name input field if index is not valid', async () => {
        // ARRANGE
        const props = {
            context: {
                model: [],
            },
        };

        const data = {
            values: [],
        };

        const wrapper = getMountedWrapper(props, data);

        // ACT
        const el = await wrapper.findAll('[data-testid="medicine-name"]').at(0);
        await el.setValue('zzzzz');
        await el.trigger('blur');
        await wrapper.findAll('[data-testid="medicine-remark"]').at(0).setValue('yyyyy');
        await wrapper.findAll('[data-testid="medicine-known-effects"]').at(0).setValue('xxxxx');

        // ASSERT
        // await expect(addItemSpy).toBeCalledTimes(1);
        // await expect(isValidSpy).toBeCalledTimes(4);
        // NOTE the spy seems to work, but if i turn it on, it seems to have an effect on the newline
        // so then the object won't match anymore.

        await expect(wrapper.vm.values).toMatchObject([
            { knownEffects: 'xxxxx', name: 'zzzzz', remark: 'yyyyy' },
            { knownEffects: null, name: '', remark: null },
        ]);
    });

    it('should give a warning when name is empty and remark or knownEffects are filled in.', async () => {
        // ARRANGE
        const props = {
            context: {
                model: [],
            },
        };

        // this needs to be instantiated as an array or else this will run into line 58 .push() only working on an array.
        const data = {
            values: [],
        };

        const wrapper = getMountedWrapper(props, data);

        // ACT
        const el = await wrapper.findAll('[data-testid="medicine-name"]').at(0);
        await el.setValue('');
        await el.trigger('blur');
        await wrapper.findAll('[data-testid="medicine-remark"]').at(0).setValue('yyyyy');
        await wrapper.findAll('[data-testid="medicine-known-effects"]').at(0).setValue('xxxxx');

        // ASSERT
        await expect(wrapper.vm.values).toMatchObject([{ knownEffects: 'xxxxx', name: '', remark: 'yyyyy' }]);
        await expect(wrapper.find('[data-testid="medicine-name"]').attributes('aria-invalid')).toBe('true');
    });

    it('should delete an added medicine onclick if model.name exists and is hovered over', async () => {
        // ARRANGE
        const props = {
            context: {
                model: [
                    {
                        name: 'aaaaa',
                        remark: 'bbbbb',
                        knownEffects: 'ccccc',
                    },
                ],
            },
        };

        const data = {
            hovered: 0,
        };

        const wrapper = getWrapper(props, data);

        await expect(wrapper.vm.values).toMatchObject([
            { knownEffects: 'ccccc', name: 'aaaaa', remark: 'bbbbb' },
            { knownEffects: null, name: '', remark: null },
        ]);

        // ACT
        await wrapper.find('BButton-stub[tag="button"]').trigger('click');

        // ASSERT
        await expect(wrapper.vm.values).toMatchObject([{ knownEffects: null, name: '', remark: null }]);
    });

    const inputDisableFields = ['medicine-name', 'medicine-remark', 'medicine-known-effects'];

    it('the input fields in inputDisableFields[] should be disabled and delete button should be absent if the disabled property is set to true', async () => {
        const props = {
            context: {
                model: [
                    {
                        name: 'aaaaa',
                        remark: 'bbbbb',
                        knownEffects: 'ccccc',
                    },
                ],
            },
            disabled: true,
        };

        const data = {
            hovered: 0,
        };

        const wrapper = await getMountedWrapper(props, data);

        await wrapper.vm.$nextTick();

        expect(wrapper.find('.icon--delete').exists()).toBe(false);
        inputDisableFields.forEach((field) => {
            expect(wrapper.find(`[data-testid=${field}]`).attributes().disabled).toBe('disabled');
        });
    });

    it('the input fields in inputDisableFields[] should not be disabled and delete button should be visible if the disabled property is set to false', async () => {
        const props = {
            context: {
                model: [
                    {
                        name: 'aaaaa',
                        remark: 'bbbbb',
                        knownEffects: 'ccccc',
                    },
                ],
            },
            disabled: false,
        };

        const data = {
            hovered: 0,
        };

        const wrapper = await getMountedWrapper(props, data);

        // Expect delete button to be visible
        expect(wrapper.find('.icon--delete').exists()).toBe(true);
        inputDisableFields.forEach((field) => {
            expect(wrapper.find(`[data-testid=${field}]`).attributes().disabled).toBe(undefined);
        });
    });
});

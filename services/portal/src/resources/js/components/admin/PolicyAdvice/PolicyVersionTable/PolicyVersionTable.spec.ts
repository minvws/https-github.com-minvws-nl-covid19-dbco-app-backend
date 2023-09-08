import type { VueConstructor } from 'vue';
import { mount } from '@vue/test-utils';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import PolicyVersionTable from './PolicyVersionTable.vue';
import { Button, Spinner, Tbody, Tr } from '@dbco/ui-library';
import * as statusAction from '@/store/useStatusAction';
import { useRouter } from '@/router/router';
import { adminApi } from '@dbco/portal-api';
import { fakePolicyVersion } from '@/utils/__fakes__/admin';
import { dateFnsFormat } from '@/filters/date/date';

vi.mock('@/router/router');

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(PolicyVersionTable, {
        localVue,
        propsData: props,
        stubs: { Backdrop: true },
    });
});

describe('PolicyVersionTable.vue', () => {
    it('should push detail view path to router when table row is clicked', async () => {
        vi.spyOn(adminApi, 'getPolicyVersions').mockImplementationOnce(() => Promise.resolve([fakePolicyVersion()]));
        const wrapper = createComponent();
        await flushCallStack();

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        tableBodyRow.vm.$emit('click');

        expect(useRouter().push).toHaveBeenCalledTimes(1);
    });

    it('should show a spinner when the loading status is "pending"', () => {
        vi.spyOn(statusAction, 'isPending').mockImplementationOnce(() => true);
        const wrapper = createComponent();
        const spinner = wrapper.findComponent(Spinner);
        expect(spinner.isVisible()).toBe(true);
    });

    it('should show a modal when the create button is clicked', async () => {
        const wrapper = createComponent();
        const createButton = wrapper.findComponent(Button);
        createButton.vm.$emit('click');
        await flushCallStack();
        const modal = wrapper.find('backdrop-stub');
        expect(modal.attributes('isopen')).toBe('true');
    });

    it('should make the "createPolicyVersion" call when the modal form is submitted with name and startDate', async () => {
        const callSpy = vi
            .spyOn(adminApi, 'createPolicyVersion')
            .mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));
        const wrapper = createComponent();
        const modal = wrapper.find('backdrop-stub');
        const nameInput = modal.find('[name="name"]');
        await nameInput.setValue(fakerjs.lorem.word());
        const dateInput = modal.find('[name="startDate"]');
        await dateInput.setValue(dateFnsFormat(fakerjs.date.soon(), 'yyyy-MM-dd'));
        const okButton = modal.findAllComponents(Button).at(2);
        await okButton.trigger('click');
        expect(callSpy).toHaveBeenCalledOnce;
    });

    it('should show error at the input when the "createPolicyVersion" call has a validation error', async () => {
        const givenNameError = fakerjs.lorem.word();
        const givenDateError = fakerjs.lorem.word();
        vi.spyOn(adminApi, 'createPolicyVersion').mockImplementationOnce(() =>
            Promise.reject({
                response: {
                    data: {
                        errors: {
                            name: givenNameError,
                            startDate: givenDateError,
                        },
                    },
                },
            })
        );
        const wrapper = createComponent();
        const modal = wrapper.find('backdrop-stub');
        const nameInput = modal.find('[name="name"]');
        await nameInput.setValue(fakerjs.lorem.word());
        const dateInput = modal.find('[name="startDate"]');
        await dateInput.setValue(dateFnsFormat(fakerjs.date.soon(), 'yyyy-MM-dd'));
        const okButton = modal.findAllComponents(Button).at(2);
        await okButton.trigger('click');
        await flushCallStack();
        const inputErrors = modal.findAll('.formulate-input-error');
        expect(inputErrors.at(0).text()).toContain(givenNameError);
        expect(inputErrors.at(1).text()).toContain(givenDateError);
        await nameInput.trigger('change');
        const inputErrors2 = modal.findAll('.formulate-input-error');
        expect(inputErrors2.length).toBe(0);
    });
});

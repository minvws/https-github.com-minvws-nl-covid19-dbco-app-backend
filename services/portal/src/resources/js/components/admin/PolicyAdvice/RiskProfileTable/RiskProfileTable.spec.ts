import type { VueConstructor } from 'vue';
import { mount } from '@vue/test-utils';
import { flushCallStack, setupTest } from '@/utils/test';
import RiskProfileTable from './RiskProfileTable.vue';
import { fakePolicyGuideline, fakePolicyVersion, fakeRiskProfile } from '@/utils/__fakes__/admin';
import { Button, Select, Tbody, Tr } from '@dbco/ui-library';
import * as showToast from '@/utils/showToast';
import { adminApi } from '@dbco/portal-api';
import { PolicyVersionStatusV1 } from '@dbco/enum';

vi.mock('@/router/router');

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(RiskProfileTable, {
        localVue,
        propsData: {
            versionStatus: PolicyVersionStatusV1.VALUE_draft,
            ...props,
        },
        stubs: { Backdrop: true, LastUpdated: true },
    });
});

describe('RiskProfileTable.vue', () => {
    it('should show table with correct number of profiles', () => {
        const wrapper = createComponent({ riskProfiles: [fakeRiskProfile(), fakeRiskProfile()], guidelines: [] });
        const tableBody = wrapper.findComponent(Tbody);
        expect(tableBody.findAllComponents(Tr).length).toBe(2);
    });

    it('should show a message when no items are found', () => {
        const wrapper = createComponent({ riskProfiles: [], guidelines: [] });
        expect(wrapper.text()).toContain('Geen risicoprofielen gevonden');
    });

    it('should show correct number of options in profile dropdown', () => {
        const wrapper = createComponent({
            riskProfiles: [fakeRiskProfile()],
            guidelines: [fakePolicyGuideline(), fakePolicyGuideline(), fakePolicyGuideline()],
        });
        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const dropdown = tableBodyRow.findComponent(Select);
        const options = dropdown.findAll('option');

        expect(options.length).toBe(3);
    });

    it('should show generic error when updateRiskProfile api call returns an error', async () => {
        const spyOnUpdate = vi.spyOn(adminApi, 'updateRiskProfile').mockImplementationOnce(() => Promise.reject());
        const spyOnToast = vi.spyOn(showToast, 'default').mockImplementationOnce(() => vi.fn());

        const wrapper = createComponent({
            riskProfiles: [fakeRiskProfile()],
            guidelines: [fakePolicyGuideline(), fakePolicyGuideline(), fakePolicyGuideline()],
        });

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const dropdown = tableBodyRow.findComponent(Select);
        const options = dropdown.findAll('option');

        await options.at(1).setSelected();
        await flushCallStack();

        expect(spyOnUpdate).toBeCalledTimes(1);
        expect(spyOnToast).toBeCalledTimes(1);
    });

    it('should show generic error when updateRiskProfile api call returns an error', async () => {
        const riskProfile = fakeRiskProfile();
        const spyOnUpdate = vi
            .spyOn(adminApi, 'updateRiskProfile')
            .mockImplementationOnce(() => Promise.resolve(riskProfile));

        const wrapper = createComponent({
            riskProfiles: [riskProfile],
            guidelines: [fakePolicyGuideline(), fakePolicyGuideline(), fakePolicyGuideline()],
        });

        wrapper.vm._setupState.errors.value = [riskProfile.uuid];

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const dropdown = tableBodyRow.findComponent(Select);
        const options = dropdown.findAll('option');

        await options.at(1).setSelected();
        await flushCallStack();

        expect(spyOnUpdate).toBeCalledTimes(1);
        expect(wrapper.vm._setupState.errors.value).toStrictEqual([]);
    });

    it('should disable profile dropdown based on disabled prop', () => {
        const wrapper = createComponent({
            riskProfiles: [fakeRiskProfile()],
            guidelines: [fakePolicyGuideline(), fakePolicyGuideline(), fakePolicyGuideline()],
            disabled: true,
        });
        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const dropdown = tableBodyRow.findComponent(Select);
        expect(dropdown.find('select').attributes('disabled')).toBe('disabled');
    });

    it('should show a modal when a change is made to a version with status active soon', async () => {
        const wrapper = createComponent({
            riskProfiles: [fakeRiskProfile()],
            versionStatus: PolicyVersionStatusV1.VALUE_active_soon,
            guidelines: [fakePolicyGuideline(), fakePolicyGuideline(), fakePolicyGuideline()],
        });

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const dropdown = tableBodyRow.findComponent(Select);
        const options = dropdown.findAll('option');

        await options.at(1).setSelected();
        await flushCallStack();

        const modal = wrapper.find('backdrop-stub');
        expect(modal.attributes('isopen')).toBe('true');
    });

    it('should handle pending updates when the status change warning modal is submitted', async () => {
        const spyOnUpdate = vi
            .spyOn(adminApi, 'updatePolicyVersion')
            .mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));
        const wrapper = createComponent({
            riskProfiles: [fakeRiskProfile()],
            versionStatus: PolicyVersionStatusV1.VALUE_active_soon,
            guidelines: [fakePolicyGuideline(), fakePolicyGuideline(), fakePolicyGuideline()],
        });

        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);
        const dropdown = tableBodyRow.findComponent(Select);
        const options = dropdown.findAll('option');

        await options.at(1).setSelected();
        await flushCallStack();

        const modal = wrapper.find('backdrop-stub');
        const okButton = modal.findAllComponents(Button).at(2);
        await okButton.trigger('click');

        expect(spyOnUpdate).toHaveBeenCalledOnce();
    });
});

import { useRoute } from '@/router/router';
import { fakePolicyGuideline, fakePolicyVersion, fakeRiskProfile } from '@/utils/__fakes__/admin';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { Button, Heading, TabPanel } from '@dbco/ui-library';
import { mount } from '@vue/test-utils';
import type { Mock } from 'vitest';
import type { VueConstructor } from 'vue';
import { adminApi } from '@dbco/portal-api';
import PolicyGuidelineTable from '../PolicyGuidelineTable/PolicyGuidelineTable.vue';
import RiskProfileTable from '../RiskProfileTable/RiskProfileTable.vue';
import PolicyVersionDetail from './PolicyVersionDetail.vue';
import * as showToast from '@/utils/showToast';
import { PolicyVersionStatusV1 } from '@dbco/enum';

vi.mock('@/router/router');

vi.mock('@dbco/portal-api/client/admin.api', () => ({
    getCalendarItems: vi.fn(() => Promise.resolve([])),
    getCalendarViews: vi.fn(() => Promise.resolve([])),
    getPolicyVersion: vi.fn(() => Promise.resolve(fakePolicyVersion())),
    getPolicyGuidelines: vi.fn(() => Promise.resolve([fakePolicyGuideline()])),
    getRiskProfiles: vi.fn(() => Promise.resolve([fakeRiskProfile()])),
    updatePolicyVersion: vi.fn(() => Promise.resolve(fakePolicyVersion())),
}));

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(PolicyVersionDetail, {
        localVue,
        propsData: props,
        stubs: { Backdrop: true, LastUpdated: true },
    });
});

describe('PolicyVersionDetail.vue', () => {
    it('should render a title', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const heading = wrapper.findComponent(Heading);
        expect(heading.exists()).toBe(true);
    });

    it('should render richtlijnen once loaded', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const guidelineTable = wrapper.findComponent(PolicyGuidelineTable);
        expect(guidelineTable.exists()).toBe(true);
    });

    it('should render Risicoprofielen once loaded', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const profileTable = wrapper.findComponent(RiskProfileTable);
        expect(profileTable.exists()).toBe(true);
    });

    it('should render an input where the name can be changed', async () => {
        const givenName = fakerjs.lorem.word();
        const updateSpy = vi
            .spyOn(adminApi, 'updatePolicyVersion')
            .mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const nameInput = wrapper.findAll('.formulate-input').at(0);
        await nameInput.vm.$emit('change', { stopPropagation: vi.fn() }, givenName);
        await flushCallStack();
        expect(updateSpy).toHaveBeenCalledTimes(2);
    });

    it('should render an input where the startDate can be changed', async () => {
        const givenStartDate = fakerjs.date.recent();
        const updateSpy = vi
            .spyOn(adminApi, 'updatePolicyVersion')
            .mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const dateInput = wrapper.findAll('.formulate-input').at(1);
        await dateInput.vm.$emit('change', { stopPropagation: vi.fn() }, givenStartDate);
        await flushCallStack();
        expect(updateSpy).toHaveBeenCalledTimes(2);
    });

    it('should not display activation button and modal if policy is not in draft', async () => {
        vi.spyOn(adminApi, 'getPolicyVersion').mockImplementationOnce(() =>
            Promise.resolve(
                fakePolicyVersion({
                    status: PolicyVersionStatusV1.VALUE_active_soon,
                })
            )
        );

        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.find('[data-testid="activation-wrapper"]').exists()).toBe(false);
    });

    it('should display activation button with differing text when the startDate is today', async () => {
        vi.spyOn(adminApi, 'getPolicyVersion').mockImplementationOnce(() =>
            Promise.resolve(
                fakePolicyVersion({
                    startDate: new Date().toDateString(),
                })
            )
        );
        vi.spyOn(adminApi, 'updatePolicyVersion').mockImplementationOnce(() =>
            Promise.resolve(
                fakePolicyVersion({
                    startDate: new Date().toDateString(),
                })
            )
        );

        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const activationWrapper = wrapper.find('[data-testid="activation-wrapper"]');
        const activationButton = activationWrapper.findComponent(Button);

        expect(activationButton.text()).toContain('activeren');
    });

    it('should show a modal when the activation button is clicked', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const activationWrapper = wrapper.find('[data-testid="activation-wrapper"]');
        const activationButton = activationWrapper.findComponent(Button);

        activationButton.vm.$emit('click');
        await flushCallStack();
        const modal = wrapper.find('backdrop-stub');
        expect(modal.attributes('isopen')).toBe('true');
    });

    it('should disable the update modal ok button when reviewed checkbox is not checked', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const modal = wrapper.find('backdrop-stub');
        const okButton = modal.findAllComponents(Button).at(2);
        expect(okButton.attributes('disabled')).toBe('disabled');
    });

    it('should enable the update modal ok button when reviewed checkbox is checked', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const modal = wrapper.find('backdrop-stub');
        const checkbox = modal.find('[type="checkbox"]');

        await checkbox.setChecked(true);

        const okButton = modal.findAllComponents(Button).at(2);
        expect(okButton.attributes('disabled')).toBeUndefined;
    });

    it('should make the "updatePolicyVersion" call when the modal form is submitted with checkbox checked', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));
        const updateSpy = vi
            .spyOn(adminApi, 'updatePolicyVersion')
            .mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));

        const wrapper = createComponent();
        await flushCallStack();

        const modal = wrapper.find('backdrop-stub');
        const checkbox = modal.find('[type="checkbox"]');
        await checkbox.setChecked(true);

        const okButton = modal.findAllComponents(Button).at(2);
        await okButton.trigger('click');

        expect(updateSpy).toHaveBeenCalledOnce;
    });

    it('should show error from backend when a startDate update fails', async () => {
        const givenErrorMessage = fakerjs.lorem.sentence();
        vi.spyOn(adminApi, 'updatePolicyVersion').mockImplementationOnce(() =>
            Promise.reject({
                response: {
                    data: {
                        errors: {
                            startDate: givenErrorMessage,
                        },
                    },
                },
            })
        );
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const dateInput = wrapper.findAll('.formulate-input').at(1);
        const formError = dateInput.find('.formulate-input-error');
        expect(formError.text()).toContain(givenErrorMessage);
    });

    it('should show error from backend when a name update fails', async () => {
        const givenErrorMessage = fakerjs.lorem.sentence();
        vi.spyOn(adminApi, 'updatePolicyVersion').mockImplementationOnce(() =>
            Promise.reject({
                response: {
                    data: {
                        errors: {
                            name: givenErrorMessage,
                        },
                    },
                },
            })
        );
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const nameInput = wrapper.findAll('.formulate-input').at(0);
        const formError = nameInput.find('.formulate-input-error');
        expect(formError.text()).toContain(givenErrorMessage);
    });

    it('should show toast when an update fails and there are no errors from the backend', async () => {
        const givenStartDate = fakerjs.date.recent();
        const spyOnToast = vi.spyOn(showToast, 'default').mockImplementationOnce(() => vi.fn());
        vi.spyOn(adminApi, 'updatePolicyVersion').mockImplementationOnce(() => Promise.reject({}));
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const dateInput = wrapper.findAll('.formulate-input').at(1);
        await dateInput.vm.$emit('change', { stopPropagation: vi.fn() }, givenStartDate);
        await flushCallStack();
        expect(spyOnToast).toBeCalledTimes(1);
    });

    it('should show contact tab detail when url includes #contact', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 }, hash: '#contact' }));

        const wrapper = createComponent();
        await flushCallStack();

        const tabPanels = wrapper.findAllComponents(TabPanel);

        expect(tabPanels.at(0).attributes('hidden')).toBe('hidden');
        expect(tabPanels.at(1).attributes('hidden')).toBe(undefined);
    });

    it.each([
        [PolicyVersionStatusV1.VALUE_active, 'disabled'],
        [PolicyVersionStatusV1.VALUE_old, 'disabled'],
        [PolicyVersionStatusV1.VALUE_draft, undefined],
        [PolicyVersionStatusV1.VALUE_active_soon, undefined],
    ])(
        'should render inputs in disabled state when the version status is active or old',
        async (givenStatus, givenDisabledAttribute) => {
            vi.spyOn(adminApi, 'getPolicyVersion').mockImplementationOnce(() =>
                Promise.resolve(fakePolicyVersion({ status: givenStatus }))
            );
            (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

            const wrapper = createComponent();
            await flushCallStack();

            const nameInput = wrapper.findAll('.formulate-input').at(0).find('input');
            const dateInput = wrapper.findAll('.formulate-input').at(1).find('input');
            expect(nameInput.attributes('disabled')).toBe(givenDisabledAttribute);
            expect(dateInput.attributes('disabled')).toBe(givenDisabledAttribute);
        }
    );

    it('should show a modal when a change is made to a version with status active soon', async () => {
        const givenStartDate = fakerjs.date.recent();
        vi.spyOn(adminApi, 'getPolicyVersion').mockImplementationOnce(() =>
            Promise.resolve(fakePolicyVersion({ status: PolicyVersionStatusV1.VALUE_active_soon }))
        );
        vi.spyOn(adminApi, 'updatePolicyVersion').mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const dateInput = wrapper.findAll('.formulate-input').at(1);
        await dateInput.vm.$emit('change', { stopPropagation: vi.fn() }, givenStartDate);
        await flushCallStack();
        const modal = wrapper.find('backdrop-stub');
        expect(modal.attributes('isopen')).toBe('true');
    });

    it('should update the policyVersion in local state when the risk profile table emits an updated version', async () => {
        const givenUpdatedVersion = fakePolicyVersion();
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));

        const wrapper = createComponent();
        await flushCallStack();

        const profileTable = wrapper.findComponent(RiskProfileTable);
        profileTable.vm.$emit('status-changed', givenUpdatedVersion);
        await flushCallStack();

        expect(wrapper.vm._setupState.policyVersion.value).toStrictEqual(givenUpdatedVersion);
    });

    it('should change status to draft when the status change warning modal is submitted', async () => {
        const givenStartDate = fakerjs.date.soon();
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid: 1 } }));
        vi.spyOn(adminApi, 'getPolicyVersion').mockImplementationOnce(() =>
            Promise.resolve(fakePolicyVersion({ status: PolicyVersionStatusV1.VALUE_active_soon }))
        );
        const updateSpy = vi
            .spyOn(adminApi, 'updatePolicyVersion')
            .mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));

        const wrapper = createComponent();
        await flushCallStack();

        const dateInput = wrapper.findAll('.formulate-input').at(1);
        await dateInput.vm.$emit('change', { stopPropagation: vi.fn() }, givenStartDate);
        await flushCallStack();

        const modal = wrapper.find('backdrop-stub');
        const okButton = modal.findAllComponents(Button).at(2);
        await okButton.trigger('click');

        expect(updateSpy).toHaveBeenCalledOnce();
    });
});

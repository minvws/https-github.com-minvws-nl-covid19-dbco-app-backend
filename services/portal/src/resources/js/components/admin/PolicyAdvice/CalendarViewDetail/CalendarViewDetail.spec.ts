import type { VueConstructor } from 'vue';
import { mount } from '@vue/test-utils';
import { flushCallStack, setupTest } from '@/utils/test';
import CalendarViewDetail from './CalendarViewDetail.vue';
import { fakeCalendarItem, fakeCalendarView, fakePolicyVersion } from '@/utils/__fakes__/admin';
import FullScreenModal from '@/components/modals/FullScreenModal/FullScreenModal.vue';
import { useRoute, useRouter } from '@/router/router';
import type { Mock } from 'vitest';
import { adminApi } from '@dbco/portal-api';
import type { CalendarView } from '@dbco/portal-api/admin.dto';
import { PolicyVersionStatusV1 } from '@dbco/enum';
import { Button } from '@dbco/ui-library';

vi.mock('@/router/router');

const givenItem = fakeCalendarItem();
const givenView = fakeCalendarView({ calendarItems: [givenItem] });
const { policyVersionUuid: versionUuid, uuid: viewUuid } = givenView;

vi.mock('@dbco/portal-api/client/admin.api', () => ({
    getCalendarItems: vi.fn(() => Promise.resolve([givenItem])),
    getCalendarView: vi.fn(() => Promise.resolve(givenView as CalendarView)),
    updateCalendarView: vi.fn(() => Promise.resolve(givenView as CalendarView)),
    updatePolicyVersion: vi.fn(() => Promise.resolve(fakePolicyVersion())),
}));

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(CalendarViewDetail, {
        localVue,
        propsData: props,
        stubs: { Backdrop: true, InfoBar: true, LastUpdated: true },
    });
});

describe('CalendarViewDetail.vue', () => {
    it('should render full screen modal with the name of the current view in the title', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, viewUuid } }));

        const wrapper = createComponent();
        await flushCallStack();

        const modal = wrapper.findComponent(FullScreenModal);

        expect(modal.find('h2').text()).toContain(givenView.label);
    });

    it('should call the vueRouter back method when the full screen modal is closed', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, viewUuid } }));
        const wrapper = createComponent();
        const modal = wrapper.findComponent(FullScreenModal);

        await modal.vm.$emit('onClose');
        await flushCallStack();

        expect(useRouter().back).toHaveBeenCalledTimes(1);
    });

    it('should show a message when no view is found', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, viewUuid } }));
        vi.spyOn(adminApi, 'getCalendarView').mockImplementationOnce(() => Promise.resolve({} as CalendarView));

        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.text()).toContain('Geen kalender view gevonden');
    });

    it('should make the calendar view update call when an item is checked', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, viewUuid } }));
        const spyOnUpdate = vi
            .spyOn(adminApi, 'updateCalendarView')
            .mockImplementationOnce(() => Promise.resolve(givenView as CalendarView));

        const wrapper = createComponent();
        await flushCallStack();

        const checkboxGroup = wrapper.find('[data-type="checkbox"]');
        checkboxGroup.vm.$emit('input', [givenView.uuid]);
        await flushCallStack();

        expect(spyOnUpdate).toHaveBeenCalledTimes(1);
    });

    it('should make the calendar view update call with an empty array when the last item is unchecked', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, viewUuid } }));
        const spyOnUpdate = vi
            .spyOn(adminApi, 'updateCalendarView')
            .mockImplementationOnce(() => Promise.resolve(givenView as CalendarView));

        const wrapper = createComponent();
        await flushCallStack();

        const checkboxGroup = wrapper.find('[data-type="checkbox"]');
        checkboxGroup.vm.$emit('input', []);
        await flushCallStack();

        expect(spyOnUpdate).toHaveBeenCalledWith({ ...givenView, ...{ calendarItems: [] } });
    });

    it.each([
        [PolicyVersionStatusV1.VALUE_active, true],
        [PolicyVersionStatusV1.VALUE_old, true],
        [PolicyVersionStatusV1.VALUE_draft, false],
        [PolicyVersionStatusV1.VALUE_active_soon, false],
    ])(
        'should render checkbox group in disabled state when the version status is active or old',
        async (givenStatus, givenDisabledAttribute) => {
            (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, viewUuid } }));
            vi.spyOn(adminApi, 'getCalendarView').mockImplementationOnce(() =>
                Promise.resolve({ ...givenView, ...{ policyVersionStatus: givenStatus } } as CalendarView)
            );

            const wrapper = createComponent();
            await flushCallStack();

            const inputs = wrapper.findAll('formulateinput-stub');
            inputs.wrappers.forEach((input) => {
                expect(input.vm.$attrs.disabled).toBe(givenDisabledAttribute);
            });
        }
    );

    it('should show modal when a version with status active_soon is changed', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, viewUuid } }));
        vi.spyOn(adminApi, 'getCalendarView').mockImplementationOnce(() =>
            Promise.resolve({
                ...givenView,
                ...{ policyVersionStatus: PolicyVersionStatusV1.VALUE_active_soon },
            } as CalendarView)
        );

        const wrapper = createComponent();
        await flushCallStack();

        const checkboxGroup = wrapper.find('[data-type="checkbox"]');
        checkboxGroup.vm.$emit('input', []);
        await flushCallStack();

        const modal = wrapper.find('backdrop-stub');
        expect(modal.attributes('isopen')).toBe('true');
    });

    it('should handle status change when the status change warning modal is submitted', async () => {
        (useRoute as Mock).mockImplementationOnce(() => ({ params: { versionUuid, viewUuid } }));
        vi.spyOn(adminApi, 'getCalendarView').mockImplementationOnce(() =>
            Promise.resolve({
                ...givenView,
                ...{ policyVersionStatus: PolicyVersionStatusV1.VALUE_active_soon },
            } as CalendarView)
        );
        const spyOnUpdate = vi
            .spyOn(adminApi, 'updatePolicyVersion')
            .mockImplementationOnce(() => Promise.resolve(fakePolicyVersion()));

        const wrapper = createComponent();
        await flushCallStack();

        const checkboxGroup = wrapper.find('[data-type="checkbox"]');
        checkboxGroup.vm.$emit('input', []);
        await flushCallStack();

        const modal = wrapper.find('backdrop-stub');
        const okButton = modal.findAllComponents(Button).at(2);
        await okButton.trigger('click');

        expect(spyOnUpdate).toHaveBeenCalledOnce();
    });
});

import type { BvModal } from 'bootstrap-vue';
import { shallowMount } from '@vue/test-utils';
import { caseApi } from '@dbco/portal-api';
import CovidCaseArchiveModal from './CovidCaseArchiveModal.vue';
import { fakerjs, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const singleCase = ['21683852-ef7b-4380-b363-92c3fb15eaf2'];
const multipleCases = ['21683852-ef7b-4380-b363-92c3fb15eaf1', '21683852-ef7b-4380-b363-92c3fb15eaf2'];

const createComponent = setupTest((localVue: VueConstructor, props: Record<string, unknown> = {}) => {
    return shallowMount<CovidCaseArchiveModal>(CovidCaseArchiveModal, {
        localVue,
        propsData: {
            ...props,
        },
        stubs: {
            BFormTextarea: true,
            BModal: true,
        },
    });
});

describe('CovidCaseArchiveModal.vue', () => {
    it('should be visible', () => {
        const wrapper = createComponent({ cases: singleCase });

        expect(wrapper.findComponent({ name: 'BModal' }).exists()).toBe(true);
    });

    it('should call caseApi.archiveCase on confirm if cases is array with single uuid', async () => {
        const wrapper = createComponent({ cases: singleCase });
        const archiveNote = fakerjs.lorem.paragraph();
        await wrapper.setData({ archiveNote });

        const modal = wrapper.findComponent({ name: 'BModal' });

        expect(modal.exists()).toBe(true);

        const apiMock = vi.spyOn(caseApi, 'archiveCases').mockResolvedValueOnce({});

        await wrapper.vm.onConfirm();

        expect(apiMock).toHaveBeenCalledWith(singleCase, archiveNote, true);
    });

    it('should supress osiris notification in caseApi when sendOsirisNotifiction is false', async () => {
        const wrapper = createComponent({ cases: singleCase });
        const archiveNote = fakerjs.lorem.paragraph();
        await wrapper.setData({ archiveNote, sendOsirisNotifiction: false });

        const modal = wrapper.findComponent({ name: 'BModal' });

        expect(modal.exists()).toBe(true);

        const apiMock = vi.spyOn(caseApi, 'archiveCases').mockResolvedValueOnce({});

        await wrapper.vm.onConfirm();

        expect(apiMock).toHaveBeenCalledWith(singleCase, archiveNote, false);
    });

    it('should call caseApi.archiveMultipleCases on confirm if cases is array with multiple uuids', async () => {
        const wrapper = createComponent({ cases: multipleCases });
        const archiveNote = fakerjs.lorem.paragraph();
        await wrapper.setData({ archiveNote });

        const modal = wrapper.findComponent({ name: 'BModal' });

        expect(modal.exists()).toBe(true);

        const apiMock = vi.spyOn(caseApi, 'archiveCases').mockResolvedValueOnce({});

        await wrapper.vm.onConfirm();

        expect(apiMock).toHaveBeenCalledWith(multipleCases, archiveNote, true);
    });

    it('should reset form state when resetModal method is fired', async () => {
        const wrapper = createComponent({ cases: multipleCases });
        await wrapper.setData({
            archiveNote: fakerjs.lorem.paragraph(),
            sendOsirisNotifiction: false,
            showRequiredMessage: true,
        });

        await wrapper.vm.resetModal();

        expect(wrapper.vm.archiveNote).toBe('');
        expect(wrapper.vm.sendOsirisNotifiction).toBe(true);
        expect(wrapper.vm.showRequiredMessage).toBe(false);
    });

    it('should render modal when show method is fired', async () => {
        const wrapper = createComponent({ cases: multipleCases });
        (wrapper.vm.$refs.modal as unknown as BvModal).show = vi.fn();

        await wrapper.vm.show();

        expect(wrapper.findComponent({ name: 'BModal' }).isVisible()).toBe(true);
    });
});

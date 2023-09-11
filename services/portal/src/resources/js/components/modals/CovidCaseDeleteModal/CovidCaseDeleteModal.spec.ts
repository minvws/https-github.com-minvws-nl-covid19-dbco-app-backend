import { shallowMount } from '@vue/test-utils';

import i18n from '@/i18n/index';

import CovidCaseDeleteModal from './CovidCaseDeleteModal.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const hide = vi.fn();
const show = vi.fn();

const createComponent = setupTest(
    (localVue: VueConstructor, props: Record<string, unknown> = {}, withDefaultStub?: boolean) => {
        const BModalMock = {
            template: '<div />',
            methods: { hide, show },
        };

        return shallowMount<CovidCaseDeleteModal>(CovidCaseDeleteModal, {
            localVue,
            i18n,
            propsData: {
                ...props,
            },
            stubs: {
                BModal: withDefaultStub ? true : BModalMock,
            },
        });
    }
);

describe('CovidCaseDeleteModal.vue', () => {
    it('should not render a modal when created', () => {
        // ARRANGE
        createComponent({ text: 'Na verwijderen is het nog 7 dagen mogelijk om dit dossier te herstellen.' });

        // ASSERT
        expect(show).not.toHaveBeenCalled();
    });

    it('should render a modal when component show method is triggered to store', async () => {
        // ARRANGE
        const wrapper = createComponent({
            text: 'Na verwijderen is het nog 7 dagen mogelijk om dit dossier te herstellen.',
        });

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        await wrapper.vm.$nextTick();
        await wrapper.vm.show();

        // ASSERT
        expect(show).toHaveBeenCalled();
    });

    it('should reset state when modal is hidden', async () => {
        // ARRANGE
        const wrapper = createComponent({
            text: 'Na verwijderen is het nog 7 dagen mogelijk om dit dossier te herstellen.',
        });

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hide');
        await wrapper.vm.$nextTick();

        expect(hide).toHaveBeenCalled();
    });
});

import { MessageTemplateTypeV1 } from '@dbco/enum';
import type { UntypedWrapper } from '@/utils/test';
import { createLocalVue, shallowMount } from '@vue/test-utils';
import BootstrapVue from 'bootstrap-vue';
import SendEmailButton from './SendEmailButton.vue';

describe('SendEmailButton.vue', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);

    const setWrapper = (props?: object) =>
        shallowMount(SendEmailButton, {
            localVue,
            propsData: props,
        }) as UntypedWrapper;

    it('should have an enabled "showModal" button by default', () => {
        const props = {
            caseUuid: '0000',
            mailVariant: MessageTemplateTypeV1.VALUE_contactInfection,
        };

        const wrapper = setWrapper(props);
        expect(wrapper.find('[data-testid="showModalButton"]').attributes('disabled')).toBe(undefined);
    });

    it('should have a disabled "showModal" button if the property "isDisabled" is passed to the component', () => {
        const props = {
            caseUuid: '0000',
            mailVariant: MessageTemplateTypeV1.VALUE_contactInfection,
            isDisabled: true,
        };

        const wrapper = setWrapper(props);

        expect(wrapper.find('[data-testid="showModalButton"]').attributes('disabled')).toBe('true');
    });

    it('should open the modal when "showModal" is pressed', async () => {
        const props = {
            caseUuid: '0000',
            mailVariant: MessageTemplateTypeV1.VALUE_contactInfection,
            isDisabled: true,
        };

        const wrapper = setWrapper(props);

        const spyBvModalShow = vi.spyOn(wrapper.vm.$bvModal, 'show');
        await wrapper.findComponent({ name: 'BButton' }).trigger('click');

        // Should have hidden list modal
        expect(spyBvModalShow).toHaveBeenNthCalledWith(1, wrapper.vm.modalId);
    });

    it('should assign "Index mailen" as modal title if taskUuid not set', () => {
        const props = {
            caseUuid: '0000',
            mailVariant: MessageTemplateTypeV1.VALUE_contactInfection,
            isDisabled: true,
        };

        const wrapper = setWrapper(props);
        const modalAttrs = wrapper.findComponent({ name: 'SendMessageModal' }).attributes();

        expect(modalAttrs.modaltitle).toBe('Index mailen');
    });

    it('should assign "Contact mailen" as modal title if taskUuid set', () => {
        const props = {
            caseUuid: '0000',
            taskUuid: '1111',
            mailVariant: MessageTemplateTypeV1.VALUE_contactInfection,
            isDisabled: true,
        };

        const wrapper = setWrapper(props);
        const modalAttrs = wrapper.findComponent({ name: 'SendMessageModal' }).attributes();

        expect(modalAttrs.modaltitle).toBe('Contact mailen');
    });
});

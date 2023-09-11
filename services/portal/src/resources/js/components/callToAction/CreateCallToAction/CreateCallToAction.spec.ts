import i18n from '@/i18n/index';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import indexStore from '@/store/index/indexStore';
import { setupTest } from '@/utils/test';
import { generateFakeCallToActionRequest } from '@/utils/__fakes__/callToAction';
import { createTestingPinia } from '@pinia/testing';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import CreateCallToAction from './CreateCallToAction.vue';

vi.mock('@/env');
vi.stubGlobal('location', { replace: vi.fn() });
const stubs = {
    BButton: true,
    BDropdown: true,
    BDropdownItem: true,
    BForm: true,
    BFormDatepicker: true,
    BFormGroup: true,
    BFormInput: true,
    BFormInvalidFeedback: true,
    BFormTextarea: true,
};

const fakeCallToActionRequest = generateFakeCallToActionRequest();

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(CreateCallToAction, {
        localVue,
        i18n,
        propsData: {
            caseUuid: fakeCallToActionRequest.resource_uuid,
        },
        pinia: createTestingPinia({
            stubActions: true,
        }),
        store: new Vuex.Store({
            modules: {
                index: {
                    ...indexStore,
                    state: {
                        ...indexStore.state,
                    },
                    getters: {
                        meta: vi.fn(() => ({
                            organisationUuid: fakeCallToActionRequest.organisation_uuid,
                        })),
                    },
                },
            },
        }),
        stubs,
    });
});

describe('CreateCallToAction.vue', () => {
    it('should render a form for creating call to actions', () => {
        // GIVEN its default state
        // WHEN the component is created
        const wrapper = createComponent();

        // THEN it should render a form for creating call to actions
        const form = wrapper.find('bform-stub');
        expect(form.exists()).toBe(true);
    });

    // inputType        | inputIndex
    it.each([
        ['role', 0],
        ['date', 1],
        ['subject', 2],
        ['description', 3],
    ])('should render a form group for providing a %s', (inputType, inputIndex) => {
        // GIVEN its default state
        // WHEN the component is created
        const wrapper = createComponent();

        // THEN it should render a form group for the given input type
        const formGroups = wrapper.findAll('bformgroup-stub');
        expect(formGroups.at(inputIndex).html()).toContain(i18n.t(`components.createCallToAction.${inputType}.label`));
    });

    it('should render a placeholder for selecting a role', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // AND there is no selected role
        await wrapper.setData({ selectedRole: undefined });

        // THEN it should render a placeholder for selecting a role
        const roleDropdown = wrapper.findAll('bformgroup-stub').at(0).find('bdropdown-stub');
        expect(roleDropdown.attributes('class')).toBe('placeholder');
        expect(roleDropdown.attributes('text')).toBe(i18n.t('components.createCallToAction.role.placeholder'));
    });

    it('should render a required message for role selection when the form is submitted without a selected role', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // AND there is no selected role
        await wrapper.setData({ selectedRole: undefined });

        //WHEN the form is submitted
        await wrapper.vm.submit();

        //THEN it should render a required message for role selection
        const message = wrapper.findAll('bformgroup-stub').at(0).find('bforminvalidfeedback-stub');
        expect(message.attributes('state')).toBeUndefined();
    });

    it('should render a placeholder for selecting a date', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // AND there is no selected date
        await wrapper.setData({ selectedDate: '' });

        // THEN it should render a placeholder for selecting a date
        const datePicker = wrapper.find('bformdatepicker-stub');
        expect(datePicker.attributes('class')).toBe('placeholder');
        expect(datePicker.attributes('placeholder')).toBe(i18n.t('components.createCallToAction.date.placeholder'));
    });

    it('should render a required message for date selection when the form is submitted without a selected date', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // AND there is no selected date
        await wrapper.setData({ selectedDate: '' });

        //WHEN the form is submitted
        await wrapper.vm.submit();

        //THEN it should render a required message for date selection
        const message = wrapper.findAll('bformgroup-stub').at(1).find('bforminvalidfeedback-stub');
        expect(message.attributes('state')).toBeUndefined();
    });

    it('should render a placeholder for subject', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // AND there is no subject
        await wrapper.setData({ subject: '' });

        // THEN it should render a placeholder for subject
        const subjectInput = wrapper.findAll('bformgroup-stub').at(2).find('bforminput-stub');
        expect(subjectInput.attributes('placeholder')).toBe(
            i18n.t('components.createCallToAction.subject.placeholder')
        );
    });

    it('should render a required message for subject when the form is submitted without a subject', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // AND there is no subject
        await wrapper.setData({ subject: '' });

        //WHEN the form is submitted
        await wrapper.vm.submit();

        //THEN it should render a required message for subject
        const message = wrapper.findAll('bformgroup-stub').at(2).find('bforminvalidfeedback-stub');
        expect(message.attributes('state')).toBeUndefined();
    });

    it('should render a placeholder for description', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // AND there is no description
        await wrapper.setData({ description: '' });

        // THEN it should render a placeholder for description
        const descriptionInput = wrapper.find('bformtextarea-stub');
        expect(descriptionInput.attributes('placeholder')).toBe(
            i18n.t('components.createCallToAction.description.placeholder')
        );
    });

    it('should render a required message for description when the form is submitted without a description', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        // AND there is no description
        await wrapper.setData({ description: '' });

        //WHEN the form is submitted
        await wrapper.vm.submit();

        //THEN it should render a required message for description
        const message = wrapper.findAll('bformgroup-stub').at(3).find('bforminvalidfeedback-stub');
        expect(message.attributes('state')).toBeUndefined();
    });

    it('should keep track of the character count for the description', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();

        //WHEN the description is updated
        await wrapper.setData({ description: fakeCallToActionRequest.description });

        //THEN it should also update the character count
        const characterCount = wrapper.find('small');
        expect(characterCount.text()).toBe(
            `${fakeCallToActionRequest.description.length}/${wrapper.vm.descriptionMaxLength}`
        );
    });

    it('should render a cancellation link that emits cancel event when clicked', async () => {
        // GIVEN its default state
        // WHEN the component is created
        const wrapper = createComponent();

        // THEN it should render a cancellation link that returns to the case
        const cancelButton = wrapper.findAll('bbutton-stub').at(0);
        // AND when clicked, it should emit a cancel event
        await cancelButton.trigger('click');

        expect(wrapper.emitted('cancel')).toBeTruthy();
    });

    it('should render a submit button', () => {
        // GIVEN its default state
        // WHEN the component is created
        const wrapper = createComponent();

        // THEN it should render a submit button
        const buttons = wrapper.findAll('bbutton-stub');
        expect(buttons.at(1).text()).toBe(i18n.t('components.createCallToAction.actions.submit'));
        expect(buttons.at(1).attributes('variant')).toBe('primary');
    });

    it('given all inputs are valid, when the form is submitted, then the create api should be called and created event should be emitted', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();
        const spyOnCreate = vi.spyOn(useCallToActionStore(), 'createCallToAction');

        // AND all inputs are valid
        const fakeData = {
            description: fakeCallToActionRequest.description,
            selectedDate: fakeCallToActionRequest.expires_at,
            selectedRole: fakeCallToActionRequest.role,
            subject: fakeCallToActionRequest.subject,
        };
        await wrapper.setData(fakeData);

        // WHEN the form is submitted
        await wrapper.vm.submit();
        await wrapper.vm.$nextTick();

        // THEN it should dispatch the create action
        expect(spyOnCreate).toHaveBeenCalledTimes(1);

        // AND have a disabled button
        const buttons = wrapper.findAll('bbutton-stub');
        expect(buttons.at(1).attributes().disabled).toBe('true');

        // And emit the created event
        expect(wrapper.emitted('created')).toBeTruthy();
    });

    it('given all inputs are invalid, when the form is submitted, then the create action should NOT be dispatched', async () => {
        // GIVEN the component is created
        const wrapper = createComponent();
        const spyOnCreate = vi.spyOn(useCallToActionStore(), 'createCallToAction');

        // AND all inputs are invalid
        const fakeData = {
            description: '',
            selectedDate: '',
            selectedRole: '',
            subject: '',
        };
        await wrapper.setData(fakeData);

        // WHEN the form is submitted
        await wrapper.vm.submit();

        // THEN it should NOT dispatch the create action
        expect(spyOnCreate).toHaveBeenCalledTimes(0);

        const buttons = wrapper.findAll('bbutton-stub');
        expect(buttons.at(1).attributes('variant')).toBe('primary');
    });
});

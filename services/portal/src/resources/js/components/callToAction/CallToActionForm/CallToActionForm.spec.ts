import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { flushCallStack, setupTest } from '@/utils/test';
import { createTestingPinia } from '@pinia/testing';

import i18n from '@/i18n/index';

import CallToActionForm from './CallToActionForm.vue';
import ChoreActions from '@/components/chore/ChoreActions/ChoreActions.vue';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import { fakeAssignedCTA, fakeCallToAction, generateFakeCallToActionResponse } from '@/utils/__fakes__/callToAction';

const stubs = {
    BButton: true,
    BForm: true,
    BFormTextarea: true,
};

const createComponent = setupTest((localVue: VueConstructor, givenPickedUpValue = false) => {
    return shallowMount<CallToActionForm>(CallToActionForm, {
        localVue,
        i18n,
        propsData: {
            callToAction: fakeCallToAction,
            pickedUp: givenPickedUpValue,
        },
        pinia: createTestingPinia({
            initialState: {
                callToAction: fakeAssignedCTA,
            },
            stubActions: false,
        }),
        stubs,
    });
});

describe('CallToActionForm.vue', () => {
    it('should show action buttons when not confirming an action', () => {
        // GIVEN we are not confirming an action
        // WHEN the component is rendered
        const wrapper = createComponent();

        // THEN it should show the action buttons
        const actionButtons = wrapper.find('choreactions-stub');
        expect(actionButtons.attributes('labelfordropaction')).toBe(
            i18n.t(`components.callToActionSidebar.actions.drop`)
        );
        expect(actionButtons.attributes('labelforpickupaction')).toBe(
            i18n.t(`components.callToActionSidebar.actions.pick_up`)
        );
        expect(actionButtons.attributes('labelfortertiaryaction')).toBe(
            i18n.t(`components.callToActionSidebar.actions.complete`)
        );
        expect(actionButtons.attributes('labelforviewlink')).toBe(
            i18n.t(`components.callToActionSidebar.actions.view`)
        );
        expect(actionButtons.attributes('viewlink')).toBe(`/editcase/${fakeCallToAction.resource.uuid}`);
    });

    it('should show a textarea with custom action buttons when confirming an action', async () => {
        // GIVEN the component is rendered
        const wrapper = createComponent();

        // WHEN we are confirming an action
        await wrapper.setData({
            confirming: {
                active: true,
                type: 'complete',
            },
        });

        // THEN it should show a textarea with custom action buttons
        const textarea = wrapper.find('#cta-form-textarea');
        const customActions = wrapper.find('#cta-form-actions');
        expect(textarea.exists()).toBe(true);
        expect(customActions.exists()).toBe(true);
    });

    it.each([['complete'], ['drop']])(
        'should show translated texts when confirming a "%s" action',
        async (actionType) => {
            // GIVEN the component is rendered
            const wrapper = createComponent();

            // WHEN we are confirming an action
            await wrapper.setData({
                confirming: {
                    active: true,
                    type: actionType,
                },
                showRequiredMessage: true,
            });

            // THEN it should show translated texts
            expect(wrapper.find('h5').text()).toBe(i18n.t(`components.callToActionSidebar.note.${actionType}.title`));
            expect(wrapper.find('bformtextarea-stub').attributes('placeholder')).toBe(
                i18n.t(`components.callToActionSidebar.note.${actionType}.placeholder`)
            );
            expect(wrapper.find('p').text()).toBe(i18n.t(`components.callToActionSidebar.note.${actionType}.required`));
            expect(wrapper.find('[type=submit]').text()).toBe(
                i18n.t(`components.callToActionSidebar.actions.confirm.${actionType}`)
            );
        }
    );

    it('should keep track of the character count for the confirmation note', async () => {
        // GIVEN the component is rendered
        const wrapper = createComponent();

        // AND we are confirming an action
        await wrapper.setData({
            confirming: {
                active: true,
                type: 'complete',
            },
        });

        //WHEN the confirmation note is updated
        await wrapper.setData({ note: wrapper.vm.$props.callToAction.description });

        //THEN it should also update the character count
        const characterCount = wrapper.find('small');
        expect(characterCount.text()).toBe(
            `${wrapper.vm.$props.callToAction.description.length}/${wrapper.vm.noteMaxLength}`
        );
    });

    it('should render a required message for the confirmation note when the form is submitted without it', async () => {
        // GIVEN the component is rendered
        const wrapper = createComponent();

        // AND we are confirming an action
        await wrapper.setData({
            confirming: {
                active: true,
                type: 'complete',
            },
        });

        //WHEN the form is submitted
        await wrapper.vm.confirm();

        //THEN it should render a required message for the confirmation note
        const message = wrapper.find('#cta-form-textarea').find('div');
        expect(message.attributes('state')).toBeUndefined();
    });

    it('should dispatch the correct store action when a pickup action occurs', async () => {
        // GIVEN a call to action that is not picked up
        // AND the component is rendered
        const wrapper = createComponent();
        const spyAction = vi.spyOn(useCallToActionStore(), 'pickupSelected');

        //WHEN a pickup action occurs
        await wrapper.findComponent(ChoreActions).vm.$emit('toggle');
        await flushCallStack();

        // THEN it should dispatch the pickup action
        expect(spyAction).toHaveBeenCalledTimes(1);
    });

    it('should render the confirmation state when a drop action occurs', async () => {
        // GIVEN a call to action that is picked up
        // AND the component is rendered
        const wrapper = createComponent(true);

        //WHEN a drop action occurs
        await wrapper.findComponent(ChoreActions).vm.$emit('toggle');

        // THEN the confirmation state should be rendered
        expect(wrapper.find('[type=submit]').text()).toBe(
            i18n.t(`components.callToActionSidebar.actions.confirm.drop`)
        );
    });

    it('should render the confirmation state when a complete action occurs', async () => {
        // GIVEN a call to action that is picked up
        // AND the component is rendered
        const wrapper = createComponent(true);

        //WHEN a complete action occurs
        await wrapper.findComponent(ChoreActions).vm.$emit('tertiaryAction');

        // THEN the confirmation state should be rendered
        expect(wrapper.find('[type=submit]').text()).toBe(
            i18n.t(`components.callToActionSidebar.actions.confirm.complete`)
        );
    });

    it('should cancel confirmation when the cancel action occurs', async () => {
        // GIVEN the component is rendered with a call to action that is picked up
        const wrapper = createComponent(true);

        // AND we are confirming an action
        await wrapper.setData({
            confirming: {
                active: true,
                type: 'complete',
            },
        });

        //WHEN a cancel action occurs
        await wrapper.vm.cancel();

        // THEN the confirmation should be cancelled and its state reset
        const textarea = wrapper.find('#cta-form-textarea');
        expect(textarea.exists()).toBe(false);
        expect(wrapper.vm.note).toBe('');
        expect(wrapper.vm.showRequiredMessage).toBe(false);
    });

    it('should cancel confirmation when a new call to action is selected', async () => {
        // GIVEN the component is rendered with a call to action that is picked up
        const wrapper = createComponent(true);

        // AND we are confirming an action
        await wrapper.setData({
            confirming: {
                active: true,
                type: 'complete',
            },
        });

        //WHEN a different call to action is selected
        await wrapper.setProps({ callToAction: generateFakeCallToActionResponse() });

        // THEN the confirmation should be cancelled and its state reset
        const textarea = wrapper.find('#cta-form-textarea');
        expect(textarea.exists()).toBe(false);
        expect(wrapper.vm.note).toBe('');
        expect(wrapper.vm.showRequiredMessage).toBe(false);
    });

    it('given a confirmation note, when a drop action is confirmed, then the correct store action should be dispatched', async () => {
        // GIVEN the component is rendered with a call to action that is picked up
        const wrapper = createComponent(true);

        const spyAction = vi.spyOn(useCallToActionStore(), 'dropSelected');
        // AND we are confirming a drop action providing a note
        await wrapper.setData({
            confirming: {
                active: true,
                type: 'drop',
            },
            note: fakeCallToAction.description,
        });

        //WHEN the drop action is confirmed
        await wrapper.vm.confirm();
        await flushCallStack();
        // THEN it should dispatch the correct store action
        expect(spyAction).toHaveBeenCalledWith(fakeCallToAction.description);
    });

    it('given a confirmation note, when a complete action is confirmed, then the correct store action should be dispatched', async () => {
        // GIVEN the component is rendered with a call to action that is picked up
        const wrapper = createComponent(true);
        const spyAction = vi.spyOn(useCallToActionStore(), 'completeSelected');

        // AND we are confirming a complete action providing a note
        await wrapper.setData({
            confirming: {
                active: true,
                type: 'complete',
            },
            note: fakeCallToAction.description,
        });

        //WHEN the drop action is confirmed
        await wrapper.vm.confirm();

        // THEN it should dispatch the correct store action
        expect(spyAction).toHaveBeenCalledWith(fakeCallToAction.description);
    });
});

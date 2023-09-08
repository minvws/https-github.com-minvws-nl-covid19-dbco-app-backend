import { createTestingPinia } from '@pinia/testing';
import { fakeAssignedCTA, fakeCallToAction } from '@/utils/__fakes__/callToAction';
import { fakerjs, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';

import CallToActionSidebar from './CallToActionSidebar.vue';
import i18n from '@/i18n/index';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { Role } from '@dbco/portal-api/user';
import Vuex from 'vuex';

const fakeUserUuid = fakerjs.string.uuid();

const createComponent = setupTest((localVue: VueConstructor, givenStoreState: object = {}) => {
    return shallowMount<CallToActionSidebar>(CallToActionSidebar, {
        localVue,
        i18n,
        pinia: createTestingPinia({
            initialState: {
                callToAction: givenStoreState,
            },
            stubActions: false,
        }),
        store: new Vuex.Store({
            modules: {
                userInfo: {
                    ...userInfoStore,
                    state: {
                        ...userInfoStore.state,
                        user: {
                            name: fakerjs.person.fullName(),
                            roles: Role.user,
                            uuid: fakeUserUuid,
                        },
                    },
                },
            },
        }),
    });
});

describe('CallToActionSidebar.vue', () => {
    it('should render with a translated title for when there is no selected call to action', () => {
        // GIVEN there is no selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent();

        // THEN it should render a translated title for when there is no selected call to action
        expect(wrapper.find('choresidebar-stub').attributes('title')).toBe(
            i18n.t('components.callToActionSidebar.titles.no_selection')
        );
    });

    it('should render with a translated title for when there is a selected call to action', () => {
        // GIVEN there is a selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent({ selected: fakeCallToAction });

        // THEN it should render a translated title for when there is a selected call to action
        expect(wrapper.find('choresidebar-stub').attributes('title')).toBe(
            i18n.t('components.callToActionSidebar.titles.selection')
        );
    });

    it('should render with a translated hint for when there is no selected call to action', () => {
        // GIVEN there is no selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent();

        // THEN it should render a translated hint for when there is no selected call to action
        expect(wrapper.find('choresidebar-stub').attributes('hint')).toBe(
            i18n.t('components.callToActionSidebar.hints.no_selection')
        );
    });

    it('should not render with a translated hint when there is a selected call to action', () => {
        // GIVEN there is a selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent({ selected: fakeCallToAction });

        // THEN it should not render a translated hint
        expect(wrapper.find('choresidebar-stub').attributes('hint')).toBe(undefined);
    });

    it('should render a close/deselect button when there is a selected call to action', () => {
        // GIVEN there is a selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent({ selected: fakeCallToAction });

        // THEN it should render a close/deselect button
        expect(wrapper.find('#deselect').exists()).toBe(true);
    });

    it('should render an info block with a translated message when there is a selected call to action', () => {
        // GIVEN there is a selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent({ selected: fakeCallToAction });

        // THEN it should render a info block with a translated message
        expect(wrapper.find('forminfo-stub').attributes('text')).toBe(
            i18n.t(`components.callToActionSidebar.hints.pick_up`)
        );
    });

    it('should not render an info block with a translated message when the selected call to action is picked up', () => {
        // GIVEN there is a selected AND picked up call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent({ selected: fakeAssignedCTA });

        // THEN it should not render a info block with a translated message
        expect(wrapper.find('forminfo-stub').exists()).toBe(false);
    });

    it('should render the call to action details when there is a selected call to action', () => {
        // GIVEN there is a selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent({ selected: fakeCallToAction });

        // THEN it should render the call to action details
        expect(wrapper.find('calltoactiondetails-stub').exists()).toBe(true);
    });

    it('should render the call to action history when there is a selected call to action', () => {
        // GIVEN there is a selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent({ selected: fakeCallToAction });

        // THEN it should render the call to action history
        expect(wrapper.find('calltoactionhistory-stub').exists()).toBe(true);
    });

    it('should render the call to action form when there is a selected call to action', () => {
        // GIVEN there is a selected call to action
        // WHEN the sidebar is rendered
        const wrapper = createComponent({ selected: fakeCallToAction });

        // THEN it should render call to action form
        expect(wrapper.find('calltoactionform-stub').exists()).toBe(true);
    });

    it('should deselect call to action when the close/deselect button is clicked', async () => {
        // GIVEN the sidebar renders with a selected call to action
        const wrapper = createComponent({ selected: fakeCallToAction });

        // WHEN the close/deselect button is clicked
        await wrapper.find('#deselect').trigger('click');

        // THEN the call to action should be deselected
        expect(wrapper.vm.$pinia.state.value.callToAction.selected).toBeNull();
    });
});

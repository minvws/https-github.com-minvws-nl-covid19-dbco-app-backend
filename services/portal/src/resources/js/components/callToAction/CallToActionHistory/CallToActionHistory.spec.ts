import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { flushCallStack, setupTest } from '@/utils/test';

import { createTestingPinia } from '@pinia/testing';

import i18n from '@/i18n/index';

import CallToActionHistory from './CallToActionHistory.vue';
import { fakeAssignedCTA, fakeCallToAction, fakeCallToActionHistoryItem } from '@/utils/__fakes__/callToAction';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';

const stubs = {
    BButton: true,
    BSpinner: true,
};

const createComponent = setupTest((localVue: VueConstructor, givenProps?: object) => {
    return shallowMount<CallToActionHistory>(CallToActionHistory, {
        localVue,
        i18n,
        propsData: givenProps,
        pinia: createTestingPinia({
            stubActions: false,
        }),
        stubs,
    });
});

describe('CallToActionHistory.vue', () => {
    it('should render a translated title', () => {
        // GIVEN a call to action
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN the component should have a fourth level heading with a translated title
        expect(wrapper.find('h4').text()).toBe(i18n.t('components.callToActionSidebar.titles.history'));
    });

    it('should not render history content when the call to action is not picked up', () => {
        // GIVEN a call to action that is not picked up
        // WHEN the component is rendered
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: false });

        // THEN it should not render the history content
        const historyContent = wrapper.find('#cta-history > div');
        expect(historyContent.text()).toBe('-');
    });

    it('should fetch new history items when the given callToAction has changed', async () => {
        // GIVEN the component renders with an initial call to action
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: true });

        await flushCallStack();

        //WHEN the given callToAction has changed
        const spyOnAction = vi.spyOn(useCallToActionStore(), 'getHistoryItems');
        await wrapper.setProps({
            callToAction: fakeAssignedCTA,
        });

        // THEN it should fetch new history items
        expect(spyOnAction).toHaveBeenCalledOnce();
    });

    it('should render a translated action with user information for fetched history item', async () => {
        // GIVEN the component renders with an initial call to action
        const wrapper = createComponent({ callToAction: fakeCallToAction, pickedUp: true });
        await flushCallStack();

        // WHEN the given callToAction has changed
        const ctaHistoryItem = fakeCallToActionHistoryItem;
        vi.spyOn(useCallToActionStore(), 'getHistoryItems').mockImplementation(() => Promise.resolve([ctaHistoryItem]));
        await wrapper.setProps({ callToAction: fakeAssignedCTA });
        await flushCallStack();

        // THEN it should render a translated action with user information for the fetched history item
        const nameAndRoles = `${ctaHistoryItem.user.name}, ${i18n.t(`roles.user`)}`;
        const expectedTranslation = i18n.t(
            `components.callToActionSidebar.history.action.${ctaHistoryItem.callToActionEvent}`,
            { nameAndRoles }
        );
        expect(wrapper.find('h5').text()).toBe(expectedTranslation);
    });
});

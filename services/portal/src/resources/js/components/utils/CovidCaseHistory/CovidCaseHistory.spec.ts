import { flushCallStack, setupTest } from '@/utils/test';

import { mount } from '@vue/test-utils';
import CovidCaseHistory from './CovidCaseHistory.vue';

import { caseApi } from '@dbco/portal-api';
import { faker } from '@faker-js/faker';
import type { VueConstructor } from 'vue';
import { createTestingPinia } from '@pinia/testing';
import i18n from '@/i18n/index';
import {
    fakeTimelineItem,
    fakeTimelineItemWithCallToAction,
    fakeTimelineItemWithCallToActionHistory,
} from '@/utils/__fakes__/timeline';
import { fakeCallToActionHistoryItem } from '@/utils/__fakes__/callToAction';

vi.mock('@dbco/portal-api/client/case.api', () => ({
    getCaseTimelinePlanner: vi.fn(() => Promise.resolve([])),
    getCaseTimeline: vi.fn(() => Promise.resolve([])),
}));

describe('CovidCaseHistory.vue', () => {
    const createComponent = setupTest((localVue: VueConstructor, plannerTimeline = false) => {
        return mount<CovidCaseHistory>(CovidCaseHistory, {
            localVue,
            i18n,
            propsData: {
                plannerTimeline: plannerTimeline ?? undefined,
                selectedCaseUuid: faker.string.uuid(),
            },
            pinia: createTestingPinia({
                stubActions: false,
            }),
            stubs: {
                BButton: true,
                BSpinner: true,
            },
        });
    });

    it('should show loader when loading', () => {
        const wrapper = createComponent();

        wrapper.vm.loading = true;

        expect(wrapper.findComponent({ name: 'BSpinner' }).exists()).toBe(true);
    });

    it('should show message when finished loading and no timeline items found', async () => {
        vi.spyOn(caseApi, 'getCaseTimeline').mockImplementationOnce(() => Promise.resolve([]));

        const wrapper = createComponent();
        await flushCallStack();

        expect(wrapper.html()).toContain(i18n.t('components.covidCaseHistory.no_history'));
    });

    it('should make correct api call based on prop', async () => {
        const spyOnApi = vi.spyOn(caseApi, 'getCaseTimelinePlanner').mockImplementationOnce(() => Promise.resolve([]));

        createComponent(true);
        await flushCallStack();

        expect(spyOnApi).toHaveBeenCalledOnce();
    });

    it('should correctly render timeline item with type Note', async () => {
        vi.spyOn(caseApi, 'getCaseTimeline').mockImplementationOnce(() => Promise.resolve([fakeTimelineItem]));

        // WHEN the component renders the timeline
        const wrapper = createComponent();
        await flushCallStack();

        const historyItems = wrapper.findAll('.history-item-container');

        expect(historyItems.at(0).html()).toContain(fakeTimelineItem.author_user);
        expect(historyItems.at(0).html()).toContain(fakeTimelineItem.time);
        expect(historyItems.at(0).html()).toContain(fakeTimelineItem.title);
    });

    it('should correctly render timeline item with type CallToAction', async () => {
        vi.spyOn(caseApi, 'getCaseTimeline').mockImplementationOnce(() =>
            Promise.resolve([fakeTimelineItemWithCallToAction])
        );

        const wrapper = createComponent();
        await flushCallStack();

        const historyItems = wrapper.findAll('.history-item');

        expect(historyItems.at(0).classes()).toContain('with-details');
        expect(wrapper.find('.note-deadline').exists()).toBe(true);
        expect(historyItems.at(0).html()).toContain(i18n.t(`components.covidCaseHistory.note_deadline`));
        expect(historyItems.at(0).html()).toContain(i18n.t(`components.covidCaseHistory.created_at`));
    });

    it('should render button for toggling the visibility of callToAction history details', async () => {
        vi.spyOn(caseApi, 'getCaseTimeline').mockImplementationOnce(() => Promise.resolve([fakeTimelineItem]));

        const wrapper = createComponent();
        await flushCallStack();

        await wrapper.setData({
            timelineItems: [fakeTimelineItemWithCallToActionHistory([fakeCallToActionHistoryItem])],
        });

        const historyItems = wrapper.findAll('.history-item');

        expect(historyItems.at(0).html()).toContain(i18n.t(`components.covidCaseHistory.show_details`));
    });
});

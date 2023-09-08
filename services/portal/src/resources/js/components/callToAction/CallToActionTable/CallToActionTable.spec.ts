import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, setupTest } from '@/utils/test';
import { createTestingPinia } from '@pinia/testing';
import i18n from '@/i18n/index';

import CallToActionTable from './CallToActionTable.vue';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import { fakeAssignedCTA, fakeCallToAction } from '@/utils/__fakes__/callToAction';
import { formatInOtherTimeZone, parseDate } from '@/utils/date';

const createComponent = setupTest((localVue: VueConstructor, givenStoreState: object = {}) => {
    return shallowMount<CallToActionTable>(CallToActionTable, {
        localVue,
        i18n,
        pinia: createTestingPinia({
            initialState: {
                callToAction: givenStoreState,
            },
            stubActions: false,
        }),
    });
});

describe('CallToActionTable.vue', () => {
    it('should render a translated status for call to actions that are not picked up', () => {
        // GIVEN a call to action that isn't picked up
        // WHEN the table is rendered
        const wrapper = createComponent({ tableContent: [fakeCallToAction] });

        // THEN it should render a translated status for the call to action
        const statusDataCell = wrapper.findAll('td').at(1);
        expect(statusDataCell.text()).toBe(i18n.t('components.choreTable.status.not_yet_picked_up'));
    });

    it('should render a translated status for call to actions that are picked up', () => {
        // GIVEN a call to action that is picked up
        // WHEN the table is rendered
        const wrapper = createComponent({ tableContent: [fakeAssignedCTA] });

        // THEN it should render a translated status for the call to action
        const statusDataCell = wrapper.findAll('td').at(1);
        expect(statusDataCell.text()).toBe(i18n.t('components.choreTable.status.picked_up_by_you'));
    });

    it('should render the selected call to action with an active class', () => {
        // GIVEN a call to action that is selected
        // WHEN the table is rendered
        const wrapper = createComponent({ selected: fakeCallToAction, tableContent: [fakeCallToAction] });

        // THEN it should render the call to action with an active class
        const callToActionInTable = wrapper.find('tbody > tr');
        expect(callToActionInTable.classes()).toContain('active');
    });

    it('should render a formatted expiration date in the table', () => {
        // GIVEN a call to action with an expiresAt date
        // WHEN the component is rendered
        const wrapper = createComponent({ tableContent: [fakeCallToAction] });

        // THEN it should render the expiresAt date in the expected format
        const expireAtInTable = wrapper.findAll('td').at(0);
        expect(expireAtInTable.text()).toBe(
            formatInOtherTimeZone(parseDate(fakeCallToAction.expiresAt), 'Europe/Lisbon', 'dd MMMM yyyy')
        );
    });

    it('should NOT render an exclamation mark in the table if the expiration date has NOT passed', () => {
        // GIVEN a call to action with an expiresAt date in the future
        // WHEN the component is rendered
        const givenCTA = { ...fakeCallToAction, ...{ expiresAt: fakerjs.date.soon().toISOString() } };
        const wrapper = createComponent({ tableContent: [givenCTA] });

        // THEN it should NOT render an exclamation mark
        const exclamationMark = wrapper.find('.icon--error-warning');
        expect(exclamationMark.exists()).toBe(false);
    });

    it('should render an exclamation mark in the table if the expiration date has passed', () => {
        // GIVEN a call to action with an expiresAt date in the past
        // WHEN the component is rendered
        const givenCTA = { ...fakeCallToAction, ...{ expiresAt: fakerjs.date.recent().toISOString() } };
        const wrapper = createComponent({ tableContent: [givenCTA] });

        // THEN it should render an exclamation mark
        const exclamationMark = wrapper.find('.icon--error-warning');
        expect(exclamationMark.exists()).toBe(true);
    });

    it('should dispatch the "select" action when a call to action in the table is clicked', async () => {
        // GIVEN the component renders with a call to action
        const wrapper = createComponent({ tableContent: [fakeCallToAction] });
        const spyAction = vi.spyOn(useCallToActionStore(), 'select');

        // WHEN the call to action is clicked
        const callToActionInTable = wrapper.find('tbody > tr');
        await callToActionInTable.trigger('click');

        // THEN the select action should have been dispatched
        expect(spyAction).toHaveBeenCalledWith(fakeCallToAction.uuid);
    });

    it('should keep the infiniteLoader active if there are more pages to load', async () => {
        // GIVEN the component renders with table data
        const wrapper = createComponent({
            selected: null,
            table: {
                infiniteId: fakerjs.number.int(),
                page: 1,
                perPage: fakerjs.number.int(),
            },
            tableContent: [fakeCallToAction],
        });

        // WHEN the infinite loader loads a page that isn't the last one
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };
        vi.spyOn(useCallToActionStore(), 'fetchTableContent').mockImplementation(() => Promise.resolve(3));
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await wrapper.vm.$nextTick();

        // THEN the infiniteLoader should still be active
        expect(stateChanger.loaded).toBeCalledTimes(1);
    });

    it('should stop the infiniteLoader when there are NO more pages to load', async () => {
        // GIVEN the component renders with table data
        const wrapper = createComponent({
            selected: null,
            table: {
                infiniteId: fakerjs.number.int(),
                page: 1,
                perPage: fakerjs.number.int(),
            },
            tableContent: [fakeCallToAction],
        });
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };

        // WHEN the infinite loader loads the last page
        vi.spyOn(useCallToActionStore(), 'fetchTableContent').mockImplementation(() => Promise.resolve(1));
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await wrapper.vm.$nextTick();

        // THEN the infiniteLoader should be done
        expect(stateChanger.complete).toBeCalledTimes(1);
    });
});

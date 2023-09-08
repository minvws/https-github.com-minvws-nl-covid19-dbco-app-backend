import { fakePlace } from '@/utils/__fakes__/place';
import { createContainer, decorateWrapper, fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import i18n from '@/i18n/index';
import PlacesOverviewTable from './PlacesOverviewTable.vue';
import placeStore from '@/store/place/placeStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import Vuex from 'vuex';
import { PermissionV1 } from '@dbco/enum';
import ResetIndexCount from '@/components/contextManager/ResetIndexCount/ResetIndexCount.vue';
import { placeApi } from '@dbco/portal-api';
import type { PlaceListResponse } from '@dbco/portal-api/place.dto';
import { PlaceSortOptions } from '@dbco/portal-api/place.dto';
import { fakeSituation } from '@/utils/__fakes__/situation';
import { VerifiedFilter } from '@dbco/portal-api/client/place.api';

const stubs = {
    InfiniteLoading: true,
    PlacesEditModal: true,
    ResetIndexCount: true,
};

const createComponent = setupTest(
    (localVue: VueConstructor, userInfoState: object = {}, query: string = fakerjs.lorem.word(3)) => {
        return shallowMount(PlacesOverviewTable, {
            localVue,
            i18n,
            attachTo: createContainer(),
            propsData: {
                listType: '',
                query,
            },
            store: new Vuex.Store({
                modules: {
                    place: placeStore,
                    userInfo: {
                        ...userInfoStore,
                        state: {
                            ...userInfoStore.state,
                            ...userInfoState,
                        },
                    },
                },
            }),
            stubs,
        });
    }
);

describe('PlacesOverviewTable.vue', () => {
    it('should load 2 table rows when 2 places are given', async () => {
        // GIVEN there are 2 places
        // WHEN the component renders
        const wrapper = createComponent();
        await wrapper.setData({ places: [fakePlace(), fakePlace()] });

        // THEN it should render 2 table rows
        const tableBody = wrapper.findComponent({ name: 'BTbody' });
        const tableRows = tableBody.findAllComponents({ name: 'BTr' });
        expect(tableRows).toHaveLength(2);
    });

    it('should show the select checkbox given a user with permission', async () => {
        // GIVEN a user with permission
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeVerify],
        });

        // WHEN the component renders a place
        await wrapper.setData({ places: [fakePlace()] });

        // THEN the select checkbox should be shown
        const selectCheckBox = wrapper.findComponent({ name: 'BThead' }).findComponent({ name: 'BFormCheckbox' });
        expect(selectCheckBox.exists()).toBe(true);
    });

    it('should NOT show the select checkbox given a user without permission', async () => {
        // GIVEN a user without permission
        const wrapper = createComponent();

        // WHEN the component renders a place
        await wrapper.setData({ places: [fakePlace()] });

        // THEN the select checkbox should NOT be shown
        const selectCheckBox = wrapper.findComponent({ name: 'BThead' }).findComponent({ name: 'BFormCheckbox' });
        expect(selectCheckBox.exists()).toBe(false);
    });

    it('should emit "selectedPlacesUpdated" when a given place is selected', async () => {
        // GIVEN a user with permission
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeVerify],
        });

        // WHEN the component renders a place
        const givenPlace = fakePlace();
        await wrapper.setData({ places: [givenPlace] });

        // AND that place is selected
        await wrapper.setData({ selected: [givenPlace.uuid] });

        // THEN selectedPlacesUpdated is emitted
        expect(wrapper.emitted().selectedPlacesUpdated).toBeDefined();
    });

    it('should show fallback message when query is cleared', async () => {
        // GIVEN a rendered table including a place
        const wrapper = createComponent();
        vi.spyOn(wrapper.vm, 'debouncedInfiniteHandler').mockImplementation(() => vi.fn());
        const givenPlace = fakePlace();
        await wrapper.setData({ places: [givenPlace] });
        const expectedMessage = 'Er zijn nog geen locaties.';
        expect(wrapper.html()).not.toContain(expectedMessage);

        // WHEN the query is cleared
        await wrapper.setProps({ query: '' });

        // THEN the fallback message is shown
        expect(wrapper.html()).toContain(expectedMessage);
    });

    it('should reset and reload table when listType changes', async () => {
        // GIVEN a rendered table including a place
        const wrapper = createComponent();
        const resetSpy = vi.spyOn(wrapper.vm, 'resetTable');
        const reloadSpy = vi.spyOn(wrapper.vm, 'infiniteHandler').mockImplementationOnce(() => Promise.resolve());
        const givenPlace = fakePlace();
        await wrapper.setData({ places: [givenPlace] });

        // WHEN the listType changes
        await wrapper.setProps({ listType: fakerjs.lorem.word() });

        // THEN the table is reset and reloaded
        expect(resetSpy).toHaveBeenCalledTimes(1);
        expect(reloadSpy).toHaveBeenCalledTimes(1);
    });

    it('should select all places when the select all checkbox is checked', async () => {
        // GIVEN a user that has permission to select
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeVerify],
        });

        // AND a rendered table including 2 places that are not yet selected
        await wrapper.setData({ places: [fakePlace(), fakePlace()] });
        expect(wrapper.vm.$data.selected).toHaveLength(0);

        // WHEN the select all checkbox is checked
        const selectAllCheckbox = wrapper.find('bthead-stub').findComponent({ name: 'BFormCheckbox' });
        await selectAllCheckbox.vm.$emit('change', true);

        // THEN the 2 given places should be selected
        expect(wrapper.vm.$data.selected).toHaveLength(2);
    });

    it('should set the indexCountSinceReset of a place to 0 when its ResetIndexCount button is clicked', async () => {
        // GIVEN a rendered table including a place
        const wrapper = createComponent();
        const givenPlace = fakePlace();
        await wrapper.setData({ places: [givenPlace] });
        expect(wrapper.vm.$data.places[0].indexCountSinceReset).toBeGreaterThan(0);

        // WHEN the place's ResetIndexCount button is clicked
        const ResetIndexCountButton = wrapper.findComponent(ResetIndexCount);
        await ResetIndexCountButton.vm.$emit('reset');

        // THEN the place's
        expect(wrapper.vm.$data.places[0].indexCountSinceReset).toBe(0);
    });

    it('should set place in Store and render in edit mode when a place edit action is clicked', async () => {
        // GIVEN a rendered table including a place and a user with permission to edit
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeEdit],
        });
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');
        const givenPlace = fakePlace();
        await wrapper.setData({ places: [givenPlace] });

        // WHEN the place's edit action is triggered
        const tableRows = wrapper.findComponent({ name: 'BTbody' }).findAllComponents({ name: 'BTr' });
        const dropdownButton = decorateWrapper(tableRows.at(0)).findByTestId('edit-place');
        await dropdownButton.vm.$emit('click');

        // THEN the place is set in Store
        expect(spyOnCommit).toHaveBeenCalledWith('place/SET_PLACE', givenPlace);
        // AND the PlacesEditModal is rendered
        expect(wrapper.findComponent({ name: 'PlacesEditModal' }).exists()).toBe(true);
    });

    it('should set selected to empty array when the emptySelected is called', async () => {
        // GIVEN a rendered table including a selected place
        const wrapper = createComponent();
        const givenPlace = fakePlace();
        await wrapper.setData({ places: [givenPlace], selected: [givenPlace.uuid] });

        // WHEN the emptySelected method is called
        await wrapper.vm.emptySelected();

        // THEN selected is set to empty array
        expect(wrapper.vm.$data.selected).toStrictEqual([]);
    });

    it('should verify an unverified place when its verify action is triggered', async () => {
        const givenPlace = { ...fakePlace(), ...{ isVerified: false } };
        vi.spyOn(placeApi, 'verify').mockImplementationOnce(() =>
            Promise.resolve({ ...givenPlace, ...{ isVerified: !givenPlace.isVerified } })
        );

        // GIVEN a rendered table including an unverified place and a user with permission to verify
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeVerify],
        });
        await wrapper.setData({ places: [givenPlace] });

        // WHEN the place's verify action is triggered
        const tableRows = wrapper.findComponent({ name: 'BTbody' }).findAllComponents({ name: 'BTr' });
        const dropdownButton = decorateWrapper(tableRows.at(0)).findByTestId('verify-place');
        await dropdownButton.vm.$emit('click');
        await flushCallStack();

        // THEN the place should be verified
        expect(wrapper.vm.$data.places[0].isVerified).toBe(true);
        expect(wrapper.html()).toContain(i18n.t('components.placesOverviewTable.actions.unverify'));
    });

    it('should unverify a verified place when its unverify action is triggered', async () => {
        const givenPlace = { ...fakePlace(), ...{ isVerified: true } };
        vi.spyOn(placeApi, 'unverify').mockImplementationOnce(() =>
            Promise.resolve({ ...givenPlace, ...{ isVerified: !givenPlace.isVerified } })
        );

        // GIVEN a rendered table including a verified place and a user with permission to verify
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeVerify],
        });
        await wrapper.setData({ places: [givenPlace] });

        // WHEN the place's unverify action is triggered
        const tableRows = wrapper.findComponent({ name: 'BTbody' }).findAllComponents({ name: 'BTr' });
        const dropdownButton = decorateWrapper(tableRows.at(0)).findByTestId('verify-place');
        await dropdownButton.vm.$emit('click');
        await flushCallStack();

        // THEN the place should be unverified
        expect(wrapper.vm.$data.places[0].isVerified).toBe(false);
        expect(wrapper.html()).toContain(i18n.t('components.placesOverviewTable.actions.verify'));
    });

    it('should emit "merge" when a place merge action is triggered', async () => {
        // GIVEN a rendered table including a place and a user with permission to merge
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeMerge],
        });
        const givenPlace = fakePlace();
        await wrapper.setData({ places: [givenPlace] });

        // WHEN the place's merge action is triggered
        const tableRows = wrapper.findComponent({ name: 'BTbody' }).findAllComponents({ name: 'BTr' });
        const dropdownButton = decorateWrapper(tableRows.at(0)).findByTestId('merge-place');
        await dropdownButton.vm.$emit('click');

        // THEN "merge" is emitted
        expect(wrapper.emitted().merge).toBeDefined();
    });

    it('should render fallback when lastIndexPresence is null', async () => {
        // GIVEN a place with lastIndexPresence set to null
        const givenPlace = { ...fakePlace(), ...{ lastIndexPresence: null } };

        // WHEN the component renders
        const wrapper = createComponent();
        await wrapper.setData({ places: [givenPlace] });

        // THEN a fallback should be rendered
        const tableBody = wrapper.find('btbody-stub');
        const lastIndexPresenceDataCell = tableBody.findAll('btd-stub').at(4);
        expect(lastIndexPresenceDataCell.text()).toBe('-');
    });

    it('should render fallback when indexCountResetAt is null', async () => {
        // GIVEN a place with indexCountResetAt set to null
        const givenPlace = { ...fakePlace(), ...{ indexCountResetAt: null } };

        // WHEN the component renders
        const wrapper = createComponent();
        await wrapper.setData({ places: [givenPlace] });

        // THEN a fallback should be rendered
        const tableBody = wrapper.find('btbody-stub');
        const indexCountResetAtDataCell = tableBody.findAll('btd-stub').at(5);
        expect(indexCountResetAtDataCell.text()).toBe('-');
    });

    it('should render a nl locale time difference string for indexCountResetAt', async () => {
        // GIVEN a place with indexCountResetAt set to now
        const givenPlace = { ...fakePlace(), ...{ indexCountResetAt: new Date() } };

        // WHEN the component renders
        const wrapper = createComponent();
        await wrapper.setData({ places: [givenPlace] });

        // THEN a nl locale string should be rendered
        const tableBody = wrapper.find('btbody-stub');
        const indexCountResetAtDataCell = tableBody.findAll('btd-stub').at(5);
        const dateFnsLocalizedString = 'minder dan een minuut'; // date util uses nl from date-fns/locale
        expect(indexCountResetAtDataCell.text()).toBe(
            i18n.t('utils.date.ago', { timeDifference: dateFnsLocalizedString })
        );
    });

    it('should navigate to place page when its row in the table is clicked', async () => {
        // GIVEN a place to render
        const wrapper = createComponent();
        const givenPlace = fakePlace();

        // WHEN that place's row is clicked
        const targetElement = document.createElement('td');
        await wrapper.vm.rowClicked(givenPlace.uuid, {
            target: targetElement,
        });

        // THEN it should navigate to that place's page
        expect(window.location.assign).toHaveBeenCalledWith(`/editplace/${givenPlace.uuid}`);
    });

    // elementType
    it.each([['a'], ['button'], ['i'], ['input'], ['label']])(
        'should NOT navigate to place page when a %s element in the table is clicked',
        async (elementType) => {
            // GIVEN a place to render
            const wrapper = createComponent();
            const givenPlace = fakePlace();

            // WHEN the elementType in the row is clicked
            const targetElement = document.createElement(elementType);
            await wrapper.vm.rowClicked(givenPlace.uuid, {
                target: targetElement,
            });

            // THEN it should NOT navigate to that place's page
            expect(window.location.assign).not.toHaveBeenCalled;
        }
    );

    it('should update selectedFilter when the isVerified filter is changed', () => {
        const wrapper = createComponent();

        // WHEN the verified filter is changed
        wrapper.vm.updateFilters({ type: 'isVerified', value: VerifiedFilter.Unverified });

        // THEN the selected value should be stored in the state
        expect(wrapper.vm.selectedFilters.isVerified).toBe(VerifiedFilter.Unverified);
    });

    it('should call API when infiniteHandler is triggered and complete the inifinite loader', async () => {
        const searchString = fakerjs.lorem.word(3);
        const complete = vi.fn();
        const placeApiSpy = vi.spyOn(placeApi, 'getPlacesByListType').mockImplementationOnce(() =>
            Promise.resolve({
                data: [],
                total: 0,
                from: 0,
                to: 0,
                currentPage: 1,
                lastPage: 1,
            } as PlaceListResponse)
        );

        const wrapper = createComponent({}, searchString);

        // WHEN inifinite scroll is triggered and a filter/query is set
        wrapper.vm.$refs.infiniteLoading = { stateChanger: { complete } };
        await wrapper.setData({ selectedFilters: { isVerified: VerifiedFilter.Unverified } });
        await wrapper.vm.infiniteHandler();

        // THEN the API search call should be made with the correct parameters
        expect(placeApiSpy).toHaveBeenCalledWith(
            1,
            '',
            searchString,
            VerifiedFilter.Unverified,
            'all',
            undefined,
            undefined,
            30
        );
        // WHEN no new pages; THEN complete should be called
        expect(complete).toBeCalled();
    });

    it('should do nothing when infiniteHandler is triggered and loading is true', async () => {
        const complete = vi.fn();
        const placeApiSpy = vi.spyOn(placeApi, 'getPlacesByListType');

        const wrapper = createComponent();

        // WHEN inifinite scroll is triggered while loading is true
        wrapper.vm.$refs.infiniteLoading = { stateChanger: { complete } };
        await wrapper.setData({ loading: true });
        await wrapper.vm.infiniteHandler();

        // THEN the API search call should NOT be made
        expect(placeApiSpy).toHaveBeenCalledTimes(0);
    });

    it('should NOT include searchString shorter than 3 characters in the API call when infiniteHandler is triggered', async () => {
        const searchString = fakerjs.lorem.word(2);
        const complete = vi.fn();
        const placeApiSpy = vi.spyOn(placeApi, 'getPlacesByListType').mockImplementationOnce(() =>
            Promise.resolve({
                data: [fakePlace()],
                total: 0,
                from: 0,
                to: 0,
                currentPage: 1,
                lastPage: 1,
            } as PlaceListResponse)
        );

        const wrapper = createComponent({}, searchString);

        // WHEN inifinite scroll is triggered
        wrapper.vm.$refs.infiniteLoading = { stateChanger: { complete } };
        await wrapper.vm.infiniteHandler();

        // THEN the API search call should be made without the searchString
        expect(placeApiSpy).toHaveBeenCalledWith(1, '', undefined, VerifiedFilter.All, 'all', undefined, undefined, 30);
    });

    it('should set the stateChanger to loaded when the infiniteHandler API call was succesful and there are more pages to load', async () => {
        const loaded = vi.fn();
        // GIVEN a succesful API call that reveals that there are more pages to load (lastPage > current table page).
        const placeApiSpy = vi.spyOn(placeApi, 'getPlacesByListType').mockImplementationOnce(() =>
            Promise.resolve({
                data: [fakePlace()],
                total: 0,
                from: 0,
                to: 0,
                currentPage: 1,
                lastPage: 3,
            } as PlaceListResponse)
        );

        const wrapper = createComponent();

        // WHEN inifinite scroll is triggered
        wrapper.vm.$refs.infiniteLoading = { stateChanger: { loaded } };
        await wrapper.vm.infiniteHandler();

        // THEN the API search call should be made
        expect(placeApiSpy).toHaveBeenCalledTimes(1);
        // WHEN there are more pages; THEN loaded should be called
        expect(loaded).toBeCalled();
    });

    it('should set sort state and call resetTable when a sortable column header is clicked', async () => {
        // GIVEN a rendered table including multiple places
        const wrapper = createComponent();
        const resetSpy = vi.spyOn(wrapper.vm, 'resetTable');
        await wrapper.setData({ places: [fakePlace(), fakePlace()] });

        // WHEN a sortable column's header is clicked
        const tableHeaders = wrapper.findComponent({ name: 'BThead' }).findAllComponents({ name: 'BTh' });
        const sortableHeader = tableHeaders.at(2);
        await sortableHeader.vm.$emit('click');

        // THEN the sort state should be set
        expect(wrapper.vm.table.sort).toBe(PlaceSortOptions.INDEX_COUNT);
        // AND resetTable should have been called
        expect(resetSpy).toHaveBeenCalledTimes(1);
    });

    it('should toggle the sort order when the column header of the active sort option is clicked', async () => {
        // GIVEN a rendered table with an active sort option
        const wrapper = createComponent();
        await wrapper.setData({
            places: [fakePlace(), fakePlace()],
            table: {
                infiniteId: Date.now(),
                page: 1,
                perPage: 30,
                sort: PlaceSortOptions.INDEX_COUNT,
                order: 'asc',
            },
        });

        // WHEN the active sortable column's header is clicked
        const tableHeaders = wrapper.findComponent({ name: 'BThead' }).findAllComponents({ name: 'BTh' });
        const sortableHeader = tableHeaders.at(2);
        await sortableHeader.vm.$emit('click');

        // THEN the sort state should be toggled
        expect(wrapper.vm.table.sort).toBe(PlaceSortOptions.INDEX_COUNT);
        expect(wrapper.vm.table.order).toBe('desc');

        // AND WHEN the same header is clicked again
        await sortableHeader.vm.$emit('click');

        // THEN the sort state should be toggled back
        expect(wrapper.vm.table.sort).toBe(PlaceSortOptions.INDEX_COUNT);
        expect(wrapper.vm.table.order).toBe('asc');
    });

    it('should not show a clickable icon when a place has no situations', async () => {
        // GIVEN a table including a place without situations
        const wrapper = createComponent();
        await wrapper.setData({ places: [{ ...fakePlace(), ...{ situationNumbers: [] } }] });

        // WHEN the table is rendered
        const situationButton = wrapper.find('#situations-button');

        // THEN the place should not have a clickable icon
        expect(situationButton.exists()).toBe(false);
    });

    it('should show a disabled icon when a place has situations and the user does not have edit permission', async () => {
        // GIVEN a table including a place with situations and a user without edit permission
        const wrapper = createComponent();
        await wrapper.setData({ places: [{ ...fakePlace(), ...{ situationNumbers: [fakeSituation()] } }] });

        // WHEN the table is rendered
        const situationButton = wrapper.find('#situations-button');

        // THEN the place should have a disabled icon
        expect(situationButton.exists()).toBe(true);
        expect(situationButton.attributes('disabled')).toBe('disabled');
    });

    it('should show a clickable icon when a place has situations and the user has edit permission', async () => {
        // GIVEN a table including a place with situations and a user with edit permission
        const wrapper = createComponent({
            permissions: [PermissionV1.VALUE_placeEdit],
        });
        const editSpy = vi.spyOn(wrapper.vm, 'editPlace');
        const givenPlace = { ...fakePlace(), ...{ situationNumbers: [fakeSituation()] } };
        await wrapper.setData({ places: [givenPlace] });

        // WHEN the table renders a clickable icon
        const situationButton = decorateWrapper(wrapper).findByTestId('situations-button');
        expect(situationButton.exists()).toBe(true);

        // AND the icon is clicked
        await situationButton.trigger('click');

        // THEN the editPlace method should have been called with the place it belongs to
        expect(editSpy).toHaveBeenCalledWith(givenPlace);
    });
});

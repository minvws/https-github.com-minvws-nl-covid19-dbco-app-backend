import i18n from '@/i18n/index';
import { RequestState } from '@/store/callcenter/callcenterStore';
import { fakerjs, setupTest } from '@/utils/test';
import { fakeSearchResult } from '@/utils/__fakes__/callcenter';
import { createTestingPinia } from '@pinia/testing';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import CallcenterSearchResults from './CallcenterSearchResults.vue';

const createComponent = setupTest((localVue: VueConstructor, callcenterStoreState: object) => {
    return shallowMount(CallcenterSearchResults, {
        localVue,
        i18n,
        pinia: createTestingPinia({
            initialState: {
                callcenter: callcenterStoreState,
            },
            stubActions: false,
        }),
        mocks: { $filters: { dateFnsFormat: vi.fn((value) => value) } },
    });
});

describe('CallcenterSearchResults.vue', () => {
    it('should show empty search placeholder by default', () => {
        const wrapper = createComponent({ searchState: RequestState.Idle, searchResults: [] });
        const placeholder = wrapper.find('.placeholder');
        expect(placeholder.find(' h3').text()).toContain('Vul alle velden in');
    });

    it('should show loading placeholder when request is pending', () => {
        const wrapper = createComponent({ searchState: RequestState.Pending, searchResults: [] });
        const placeholder = wrapper.find('.placeholder');
        expect(placeholder.find(' h3').text()).toContain('...');
    });

    it('should show extra details needed placeholder when no results', () => {
        const wrapper = createComponent({ searchState: RequestState.Resolved, searchResults: [] });
        const moreData = wrapper.find('.placeholder');
        expect(moreData.find('h3').text()).toContain('Er zijn extra gegevens nodig');
    });

    it('should show STILL extra details needed placeholder when no results and all fields have search data', () => {
        const wrapper = createComponent({
            searchState: RequestState.Resolved,
            searchResults: [],
            searchedAllFields: true,
        });

        const noData = wrapper.find('.placeholder');
        expect(noData.find('h3').text()).toContain('Nog geen resultaat gevonden');
        expect(noData.find('p').text()).toContain('Mogelijk is een van de velden niet goed ingevuld.');
    });

    it('should render list of search results', () => {
        const searchResults = [fakeSearchResult(), fakeSearchResult()];
        const wrapper = createComponent({
            searchState: RequestState.Resolved,
            searchResults: searchResults,
        });

        const cases = wrapper.findAllByTestId('search-result-item');
        expect(cases.length).toBe(searchResults.length);
    });

    it('should show "extra details" info message when results are found', () => {
        const wrapper = createComponent({
            searchState: RequestState.Resolved,
            searchResults: [fakeSearchResult()],
        });

        expect(wrapper.findByTestId('extra-details-info').isVisible()).toBe(true);
    });

    it('should show "still not found?" info message when results found and all fields have search data', () => {
        const wrapper = createComponent({
            searchState: RequestState.Resolved,
            searchResults: [
                fakeSearchResult({
                    personalDetails: [
                        { key: 'dateOfBirth', value: fakerjs.date.past().toDateString(), isMatch: true },
                        { key: 'lastThreeBsnDigits', value: fakerjs.string.numeric(3), isMatch: true },
                        { key: 'address', value: fakerjs.location.streetAddress(), isMatch: true },
                        { key: 'lastname', value: fakerjs.person.lastName(), isMatch: true },
                        { key: 'phone', value: fakerjs.phone.number(), isMatch: true },
                    ],
                }),
            ],
            searchedAllFields: true,
        });

        expect(wrapper.findByTestId('still-not-found-info').isVisible()).toBe(true);
    });

    it('should show singular header title with one result', () => {
        const wrapper = createComponent({
            searchState: RequestState.Resolved,
            searchResults: [fakeSearchResult()],
        });

        const headerTitle = wrapper.findByTestId('header-title');
        expect(headerTitle.text()).toBe('1 dossier gevonden');
    });

    it('should show plural header title with multiple results', () => {
        const wrapper = createComponent({
            searchState: RequestState.Resolved,
            searchResults: [fakeSearchResult(), fakeSearchResult()],
        });

        const headerTitle = wrapper.findByTestId('header-title');
        expect(headerTitle.text()).toBe('2 dossiers gevonden');
    });
});

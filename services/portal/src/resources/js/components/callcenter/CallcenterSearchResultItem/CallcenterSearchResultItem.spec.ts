import { assignmentApi, caseApi } from '@dbco/portal-api';
import i18n from '@/i18n/index';
import { RequestState } from '@/store/callcenter/callcenterStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { PermissionV1, CaseNoteTypeV1 } from '@dbco/enum';
import * as showToast from '@/utils/showToast';
import { decorateWrapper, fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { fakeSearchResult, fakeSearchResultWithoutTestDate } from '@/utils/__fakes__/callcenter';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import CallcenterSearchResultItem from './CallcenterSearchResultItem.vue';

const fieldTitles = {
    dateOfBirth: 'Geboortedatum',
    lastThreeBsnDigits: 'Laatste 3 cijfers BSN',
    address: 'Adres',
    lastname: 'Achternaam',
    phone: 'Telefoonnummer',
};

const createComponent = setupTest(
    (localVue: VueConstructor, givenProps: object, userInfoStoreState: Record<string, unknown> = {}) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoStoreState,
            },
        };

        return mount(CallcenterSearchResultItem, {
            localVue,
            i18n,
            propsData: givenProps,
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                },
            }),
            pinia: createTestingPinia({
                initialState: {
                    callcenter: { searchState: RequestState.Resolved },
                },
                stubActions: false,
            }),
            mocks: { $filters: { dateFnsFormat: vi.fn((value) => value) } },
            stubs: { CreateCallToAction: true },
        });
    }
);

describe('CallcenterSearchResults.vue', () => {
    it('should render personal details with value for matching results', () => {
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });

        const personalDetails = wrapper.findAll('li');
        expect(personalDetails.length).toBe(searchResult.personalDetails.length);

        searchResult.personalDetails.forEach((detail, index) => {
            expect(personalDetails.at(index).find('.table-list-label').text()).toContain(fieldTitles[detail.key]);
            expect(personalDetails.at(index).find('.table-list-value').text()).toContain(detail.value);
        });
    });

    it('should render personal details with info message for non matching results', () => {
        const searchResult = fakeSearchResult({
            personalDetails: [
                { key: 'dateOfBirth', value: fakerjs.date.past().toDateString(), isMatch: false },
                { key: 'lastThreeBsnDigits', value: fakerjs.string.numeric(3), isMatch: false },
                { key: 'address', value: fakerjs.location.streetAddress(), isMatch: false },
                { key: 'lastname', value: fakerjs.location.streetAddress(), isMatch: false },
                { key: 'phone', value: fakerjs.phone.number(), isMatch: false },
            ],
        });
        const wrapper = createComponent({ item: searchResult });

        const personalDetails = wrapper.findAll('li');

        searchResult.personalDetails.forEach((detail, index) => {
            expect(personalDetails.at(index).find('.table-list-label').text()).toContain(fieldTitles[detail.key]);
            expect(personalDetails.at(index).find('.table-list-value').text()).toBe(
                'Komt niet overeen of staat niet in het dossier'
            );
        });
    });

    it('should show index case when caseType is INDEX', () => {
        const wrapper = createComponent({ item: fakeSearchResult() });
        expect(wrapper.find('h4').text()).toContain('Indexdossier');
        expect(wrapper.find('span').text()).toContain('Testdatum');
    });

    it('should show contact case when caseType is CONTACT', () => {
        const wrapper = createComponent({ item: fakeSearchResult({ caseType: 'contact' }) });

        expect(wrapper.find('h4').text()).toContain('Contactdossier');
        expect(wrapper.find('span').text()).toContain('Laatste contactdatum');
    });

    it('should toggle "notitie form" for search result when "place a note" button is clicked', async () => {
        // GIVEN a search result
        const wrapper = createComponent({ item: fakeSearchResult() });

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');

        // THEN the "notitie form" should be visible
        const addNoteForm = decorateWrapper(wrapper.find('.form-container'));
        expect(addNoteForm.find('.form-container').exists()).toBe(true);
        expect(addNoteForm.find('[name="note"]').isVisible()).toBe(true);
        expect(addNoteForm.findByTestId('add-note-button').isVisible()).toBe(true);
        expect(addNoteForm.findByTestId('cancel-note-button').isVisible()).toBe(true);
    });

    it('should hide "notitie form" for search result when "cancel" is clicked', async () => {
        // GIVEN a search result
        const wrapper = createComponent({ item: fakeSearchResult() });

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        // AND the "cancel" button is then clicked
        await wrapper.findByTestId('cancel-note-button').trigger('click');
        // THEN the "notitie form" should be hidden
        expect(wrapper.find('.form-container').exists()).toBe(false);
    });

    it('should show original "place a note" button for search result when "cancel" is clicked', async () => {
        // GIVEN a search result
        const wrapper = createComponent({ item: fakeSearchResult() });

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        // AND the "cancel" button is then clicked
        await wrapper.findByTestId('cancel-note-button').trigger('click');
        // THEN the "place a note" toggle button should be visible again
        expect(wrapper.findByTestId('show-add-note-button').isVisible()).toBe(true);
    });

    it('should show empty "notitie form" for search result when "notitie form" is reopened', async () => {
        // GIVEN a search result
        const wrapper = createComponent({ item: fakeSearchResult() });

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        // AND the "cancel" button is then clicked
        await wrapper.findByTestId('cancel-note-button').trigger('click');
        // AND the "place a note" button is then clicked again
        await wrapper.findByTestId('show-add-note-button').trigger('click');

        // THEN the "notitie form" should be visible
        expect(wrapper.find('.form-container').exists()).toBe(true);
        expect(wrapper.find('[name="note"]').isVisible()).toBe(true);
        expect(wrapper.findByTestId('add-note-button').isVisible()).toBe(true);
        expect(wrapper.findByTestId('cancel-note-button').isVisible()).toBe(true);
    });

    it('should show success toast message when create note is successful', async () => {
        // GIVEN a search result
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        const noteText = fakerjs.lorem.sentence();
        const spyOnAddNote = vi.spyOn(caseApi, 'addCaseNote').mockImplementationOnce(() => Promise.resolve());
        const toastSpy = vi.spyOn(showToast, 'default').mockImplementationOnce(() => vi.fn());

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        await wrapper.find('[name="note"]').setValue(noteText);

        // AND the "add note" is triggered *(via vue formulate submit)
        await wrapper.vm._setupState.addNote(searchResult);

        // THEN addCaseNote api call is called
        expect(spyOnAddNote).toBeCalledTimes(1);
        expect(spyOnAddNote).toBeCalledWith(
            searchResult.uuid,
            noteText,
            CaseNoteTypeV1.VALUE_case_note_index_by_search,
            searchResult.token
        );
        // AND a success toast is shown
        expect(toastSpy).toBeCalledTimes(1);
        expect(toastSpy).toBeCalledWith('Notitie is geplaatst', 'callcenter-add-note-toast');
    });

    it('should close "notitie form" when create note is successful', async () => {
        // GIVEN a search result
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        const noteText = fakerjs.lorem.sentence();
        vi.spyOn(caseApi, 'addCaseNote').mockImplementationOnce(() => Promise.resolve());

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        await wrapper.find('[name="note"]').setValue(noteText);

        // AND the "add note" is triggered *(via vue formulate submit)
        await wrapper.vm._setupState.addNote(searchResult);

        // THEN the "notitie form" should be hidden
        expect(wrapper.find('.form-container').exists()).toBe(false);
    });

    it('should show original "place a note" when create note is successful', async () => {
        // GIVEN a search result
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        const noteText = fakerjs.lorem.sentence();
        vi.spyOn(caseApi, 'addCaseNote').mockImplementationOnce(() => Promise.resolve());

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        await wrapper.find('[name="note"]').setValue(noteText);

        // AND the "add note" is triggered *(via vue formulate submit)
        await wrapper.vm._setupState.addNote(searchResult);

        // THEN the original "place a note button" should be visible
        expect(wrapper.findByTestId('show-add-note-button').isVisible()).toBe(true);
    });

    it('should show error toast message when create note is unsuccessful', async () => {
        // GIVEN a search result
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        const noteText = fakerjs.lorem.sentence();
        vi.spyOn(caseApi, 'addCaseNote').mockImplementationOnce(() => Promise.reject());
        const toastSpy = vi.spyOn(showToast, 'default').mockImplementationOnce(() => vi.fn());

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        await wrapper.find('[name="note"]').setValue(noteText);

        // AND the "add note" is triggered *(via vue formulate submit)
        // AND the api result is rejected
        await wrapper.vm._setupState.addNote(searchResult.uuid, noteText);

        // THEN an error toast is shown
        expect(toastSpy).toBeCalledTimes(1);
        expect(toastSpy).toBeCalledWith('Er ging iets mis. Probeer het opnieuw.', 'callcenter-add-note-toast', true);
    });

    it('should keep showing "notitie form" when create note is unsuccessful', async () => {
        // GIVEN a search result
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        const noteText = fakerjs.lorem.sentence();
        vi.spyOn(caseApi, 'addCaseNote').mockImplementationOnce(() => Promise.reject());

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        await wrapper.find('[name="note"]').setValue(noteText);

        // AND the "add note" is triggered *(via vue formulate submit)
        // AND the api result is rejected
        await wrapper.vm._setupState.addNote(searchResult);

        // THEN the "notitie form" should still be visible
        expect(wrapper.find('.form-container').exists()).toBe(true);
        expect(wrapper.find('[name="note"]').isVisible()).toBe(true);
        expect(wrapper.findByTestId('add-note-button').isVisible()).toBe(true);
        expect(wrapper.findByTestId('cancel-note-button').isVisible()).toBe(true);
    });

    it('should not add new note if "note" field is empty when clicking on submit', async () => {
        // GIVEN a search result
        const wrapper = createComponent({ item: fakeSearchResult() });
        const spyOnAddNote = vi.spyOn(wrapper.vm._setupState.callcenterStore, 'addNote');

        // WHEN the "place a note" toggle button is clicked
        // AND no new note text is entered
        await wrapper.findByTestId('show-add-note-button').trigger('click');

        // THEN the "notitie form" should still be visible
        expect(wrapper.find('.form-container').exists()).toBe(true);
        // AND no new note should be added to the store
        expect(spyOnAddNote).not.toBeCalled();
    });

    it('should disabled "add note" button when saving is pending', async () => {
        // GIVEN a search result
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        const noteText = fakerjs.lorem.sentence();
        vi.spyOn(wrapper.vm._setupState.callcenterStore, 'addNote').mockImplementationOnce(() => new Promise(() => {}));

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        await wrapper.find('[name="note"]').setValue(noteText);

        // AND the "add note" is triggered *(via vue formulate submit)
        // AND the api result is unresolved / still pending
        wrapper.vm._setupState.addNote(searchResult);
        await wrapper.vm.$nextTick();

        // THEN the "add note" button should be disabled
        expect(wrapper.findByTestId('add-note-button').attributes('disabled')).toBe('disabled');
    });

    it('should re-enable "add note" button when saving is successful', async () => {
        // GIVEN a search result
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        const noteText = fakerjs.lorem.sentence();
        vi.spyOn(wrapper.vm._setupState.callcenterStore, 'addNote').mockImplementationOnce(() => Promise.resolve());

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        await wrapper.find('[name="note"]').setValue(noteText);

        // AND the "add note" is triggered *(via vue formulate submit)
        await wrapper.vm._setupState.addNote(searchResult);

        // AND the "place a note" button is re-clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        // THEN the "add note" button should be enabled
        expect(wrapper.findByTestId('add-note-button').attributes('disabled')).toBe(undefined);
    });

    it('should re-enable "add note" button when saving is unsuccessful', async () => {
        // GIVEN a search result
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        const noteText = fakerjs.lorem.sentence();
        vi.spyOn(wrapper.vm._setupState.callcenterStore, 'addNote').mockImplementationOnce(() => Promise.reject());

        // WHEN the "place a note" toggle button is clicked
        await wrapper.findByTestId('show-add-note-button').trigger('click');
        await wrapper.find('[name="note"]').setValue(noteText);

        // AND the "add note" is triggered *(via vue formulate submit)
        // AND the api result is rejected
        await wrapper.vm._setupState.addNote(searchResult);
        // THEN the "add note" button should be re-enabled
        expect(wrapper.findByTestId('add-note-button').attributes('disabled')).toBe(undefined);
    });

    it('should show test date if test date is provided', () => {
        const searchResult = fakeSearchResult();
        const wrapper = createComponent({ item: searchResult });
        expect(wrapper.findByTestId('test-date').text()).toBe(searchResult.testDate);
    });

    it('should show "Niet ingevuld" if no test date is provided', () => {
        const wrapper = createComponent({ item: fakeSearchResultWithoutTestDate() });
        expect(wrapper.findByTestId('test-date').text()).toBe('Niet ingevuld');
    });

    it('should show "Dossier bekijken" button if the permission for case user edit is present', () => {
        const wrapper = createComponent(
            { item: fakeSearchResult() },
            { permissions: [PermissionV1.VALUE_caseEditViaSearchCase] }
        );
        expect(wrapper.findByTestId('view-case-button').exists()).toBe(true);
    });

    it('should not show "Dossier bekijken" button if the permission for creating cases is not present', () => {
        const wrapper = createComponent({ item: fakeSearchResult() });
        expect(wrapper.findByTestId('view-case-button').exists()).toBe(false);
    });

    it('should redirect when "Dossier bekijken" button click is successful', async () => {
        // GIVEN a search result as expert callcenter user
        const searchResult = fakeSearchResult();
        const wrapper = createComponent(
            { item: searchResult },
            { permissions: [PermissionV1.VALUE_caseEditViaSearchCase] }
        );
        const windowOpenSpy = vi.spyOn(window as any, 'open').mockImplementationOnce(() => vi.fn());
        vi.spyOn(assignmentApi, 'getAccessToCase').mockImplementationOnce(() => Promise.resolve());

        // WHEN the "dossier bekijken" button is clicked for a case
        await wrapper.findByTestId('view-case-button').trigger('click');

        await flushCallStack();

        // THEN redirect should be called
        expect(windowOpenSpy).toHaveBeenCalled();
        expect(windowOpenSpy).toHaveBeenCalledWith(`/editcase/${searchResult.uuid}`, '_blank');
    });

    it('should show error toast message when "Dossier bekijken" button click is unsuccessful', async () => {
        // GIVEN a search result as expert callcenter user
        const wrapper = createComponent(
            { item: fakeSearchResult() },
            { permissions: [PermissionV1.VALUE_caseEditViaSearchCase] }
        );

        vi.spyOn(assignmentApi, 'getAccessToCase').mockImplementationOnce(() => Promise.reject());
        const toastSpy = vi.spyOn(showToast, 'default').mockImplementationOnce(() => vi.fn());

        // WHEN the "dossier bekijken" button is clicked for a case
        await wrapper.findByTestId('view-case-button').trigger('click');

        await flushCallStack();

        // THEN an error toast is shown
        expect(toastSpy).toBeCalledTimes(1);
        expect(toastSpy).toBeCalledWith('Er ging iets mis. Probeer het opnieuw.', 'callcenter-open-case-toast', true);
    });

    it('should show "task form" for search result when "add task" button is clicked', async () => {
        // GIVEN a search result
        const wrapper = createComponent({ item: fakeSearchResult() });
        // WHEN the "add task" toggle button is clicked
        await wrapper.findByTestId('show-add-task-button').trigger('click');
        // THEN the "notitie form" should be visible
        expect(wrapper.findAllByTestId('call-to-action').exists()).toBe(true);
    });
});

import Vuex from 'vuex';

import { mount } from '@vue/test-utils';
import type { CurrentSection } from '../sectionManagementTypes';
import SectionManager from './SectionManager.vue';
import placeStore from '@/store/place/placeStore';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const placeSections = [
    { label: 'Entree', uuid: '6d32fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Entree', uuid: '8c22fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Toilet', uuid: '7441dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
    { label: 'Kleedkamer', uuid: '6b12fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Zonnebank', uuid: '5364dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
];

const placeSectionsWithNoSectionEntryAtStart = [
    {
        label: 'Geen afdeling, team of klas geselecteerd',
        indexCount: 0,
        uuid: 'no-section-entry-uuid',
    },
    { label: 'Entree', uuid: '8c22fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Toilet', uuid: '7441dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
    { label: 'Kleedkamer', uuid: '6b12fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Zonnebank', uuid: '5364dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 2, hasCalculatedIndex: true },
];

const placeSectionsWithNoSectionEntryAtEnd = [
    { label: 'Entree', uuid: '8c22fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Toilet', uuid: '7441dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
    { label: 'Kleedkamer', uuid: '6b12fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Zonnebank', uuid: '5364dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 2, hasCalculatedIndex: true },
    {
        label: 'Geen afdeling, team of klas geselecteerd',
        indexCount: 0,
        uuid: 'no-section-entry-uuid',
    },
];

const alphabeticallySortedSections: CurrentSection[] = [
    { label: 'Entree', uuid: '6d32fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Entree', uuid: '8c22fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Kleedkamer', uuid: '6b12fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Toilet', uuid: '7441dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
    { label: 'Zonnebank', uuid: '5364dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
];

const sortedSectionsWithNoSectionEntry: CurrentSection[] = [
    {
        label: 'Geen afdeling, team of klas geselecteerd',
        indexCount: 0,
        uuid: 'no-section-entry-uuid',
    },
    { label: 'Entree', uuid: '8c22fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Kleedkamer', uuid: '6b12fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Toilet', uuid: '7441dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
    {
        label: 'Zonnebank',
        uuid: '5364dab3-b21d-407b-86ac-b6ba7e16ee29',
        indexCount: 2,
        hasCalculatedIndex: true,
    },
];

const createComponent = setupTest((localVue: VueConstructor, mockSections: CurrentSection[]) => {
    const placeStoreModule = {
        ...placeStore,
        state: {
            ...placeStore.state,
            ...{
                current: {},
                sections: {
                    callQueue: {
                        changeLabelQueue: [],
                        createQueue: [],
                        mergeQueue: [],
                    },
                    current: mockSections,
                },
            },
        },
    };
    return mount<SectionManager>(SectionManager, {
        localVue,
        store: new Vuex.Store({
            modules: {
                place: placeStoreModule,
            },
        }),
    });
});

describe('SectionManager.vue', () => {
    it.each([
        [placeSections, alphabeticallySortedSections],
        [placeSectionsWithNoSectionEntryAtStart, sortedSectionsWithNoSectionEntry],
        [placeSectionsWithNoSectionEntryAtEnd, sortedSectionsWithNoSectionEntry],
    ])(
        'should sort sections alphabetically, while keeping "no section entry" at the start',
        (sectionsToSort, expectedResult) => {
            const wrapper = createComponent(sectionsToSort);
            expect(wrapper.vm.filteredSections).toStrictEqual(expectedResult);
        }
    );

    it('should NOT display add-section-button when searchString is similar to an existing label', async () => {
        const wrapper = createComponent(placeSections);

        const searchStringInput = wrapper.find('[data-testid="search-string-input"]');
        await searchStringInput.setValue('Toilet');

        // This field has a ~DEBOUNCE~ of 300ms
        await new Promise((r) => setTimeout(r, 300));

        const addSectionButton = wrapper.find('[data-testid="add-section-button-wrapper"]');

        expect(addSectionButton.exists()).toBe(false);
    });

    it('should show add-section-button when searchString is NOT similar to an existing label', async () => {
        const wrapper = createComponent(placeSections);

        const searchStringInput = wrapper.find('[data-testid="search-string-input"]');
        await searchStringInput.setValue('Random');

        // This field has a ~DEBOUNCE~ of 300ms
        await new Promise((r) => setTimeout(r, 300));

        const addSectionButton = wrapper.find('[data-testid="add-section-button-wrapper"]');

        expect(addSectionButton.exists()).toBe(true);
    });

    it('should commit "ADD_SECTION" store mutation and clear searchString when a section is added', async () => {
        const wrapper = createComponent(placeSections);
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        const searchStringInput = wrapper.find('[data-testid="search-string-input"]');
        await searchStringInput.setValue('Random');

        // This field has a ~DEBOUNCE~ of 300ms
        await new Promise((r) => setTimeout(r, 300));

        const addSectionButton = wrapper.find('[data-testid="add-section-button"]');
        await addSectionButton.trigger('click');

        await wrapper.vm.$nextTick();

        expect(spyOnCommit).toHaveBeenCalledWith('place/ADD_SECTION', 'Random');

        expect(wrapper.vm.searchString).toEqual('');
        expect((searchStringInput.element as HTMLInputElement).value).toEqual('');
    });

    it('should NOT commit "ADD_SECTION" store mutation or clear searchString if label already taken', async () => {
        const wrapper = createComponent(placeSections);
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        const searchStringInput = wrapper.find('[data-testid="search-string-input"]');
        await searchStringInput.setValue('Toilet');

        // This field has a ~DEBOUNCE~ of 300ms
        await new Promise((r) => setTimeout(r, 300));

        await wrapper.vm.addSection();

        await wrapper.vm.$nextTick();

        expect(spyOnCommit).toHaveBeenCalledTimes(0);

        expect(wrapper.vm.searchString).toEqual('Toilet');
        expect((searchStringInput.element as HTMLInputElement).value).toEqual('Toilet');
    });

    it('should commit "CHANGE_SECTION_LABEL" store mutation and set "changeLabelSection" to "null" when "changeLabel" method is fired', async () => {
        const wrapper = createComponent(placeSections);
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        await wrapper.vm.changeLabel(placeSections[0].uuid);

        await wrapper.vm.$nextTick();

        expect(spyOnCommit).toHaveBeenCalledWith('place/CHANGE_SECTION_LABEL', {
            label: wrapper.vm.labelToChange,
            uuid: placeSections[0].uuid,
        });
        expect(wrapper.vm.changeLabelSection).toEqual(null);
    });

    it('should add section to "selectedSections" array when checked and remove when checked again', async () => {
        const wrapper = createComponent(placeSections);

        const checkboxWrapper = wrapper.find('[data-testlabel="Toilet"]');
        expect(checkboxWrapper.exists()).toBe(true);
        const checkbox = checkboxWrapper.find('input[type="checkbox"]');
        expect(checkbox.exists()).toBe(true);

        await checkbox.setChecked(true);

        expect(wrapper.vm.selectedSections).toEqual([placeSections[2]]);

        await checkbox.setChecked(false);

        expect(wrapper.vm.selectedSections).toEqual([]);
    });

    it('should commit "MERGE_SECTIONS" store mutation and clear "selectedSections" when "triggerMerge" method is fired', async () => {
        const wrapper = createComponent(placeSections);
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        await wrapper.vm.triggerMerge(placeSections[0], [placeSections[1]]);

        await wrapper.vm.$nextTick();

        expect(spyOnCommit).toHaveBeenCalledWith('place/MERGE_SECTIONS', {
            mainSection: placeSections[0],
            mergeSections: [placeSections[1]],
        });
        expect(wrapper.vm.selectedSections).toEqual([]);
    });

    it('should watch "changeLabelSection" and set "labelToChange" to its label when not null', async () => {
        const wrapper = createComponent(placeSections);

        await wrapper.setData({ changeLabelSection: placeSections[0] });

        expect(wrapper.vm.labelToChange).toEqual(placeSections[0].label);

        await wrapper.setData({ changeLabelSection: null });

        expect(wrapper.vm.labelToChange).toEqual('');
    });
});

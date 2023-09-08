import { createContainer, flushCallStack, setupTest } from '@/utils/test';
import { mount } from '@vue/test-utils';

import ContextSectionsForm from './ContextSectionsForm.vue';
import type { VueConstructor } from 'vue';

const placeSections = [
    {
        label: 'C1',
        uuid: '115d99c5-9b68-438d-9540-41f5c925e8a9',
        indexCount: 1,
    },
    {
        label: 'A2',
        uuid: '7d2bc0e5-fbe2-4a51-8ff2-1e4217e5f9e4',
        indexCount: 1,
    },
    {
        label: 'A2',
        uuid: 'asfd4353-fbe2-4a51-8ff2-1e4217e5f9e4',
        indexCount: 1,
    },
    {
        label: 'A3',
        uuid: '853492ab-b3c2-485a-9b55-fee0f2c15671',
        indexCount: 1,
    },
    {
        label: 'B1',
        uuid: '8af7e1d9-9413-4c75-a90c-56a2bcb8c70c',
        indexCount: 1,
    },
    {
        label: 'B2',
        uuid: '9fd0fe06-1560-47b6-a428-da9fa5edea7a',
        indexCount: 1,
    },
    {
        label: 'C2',
        uuid: 'da129a53-212a-46db-8ad1-deec4c20648b',
        indexCount: 1,
    },
    {
        label: 'A1',
        uuid: 'eed6e74f-3dac-46bf-9b30-6a7a385a3f1f',
        indexCount: 2,
    },
];

const contextSections = [
    {
        label: 'A2',
        uuid: '7d2bc0e5-fbe2-4a51-8ff2-1e4217e5f9e4',
        indexCount: 1,
    },
    {
        label: 'B1',
        uuid: '8af7e1d9-9413-4c75-a90c-56a2bcb8c70c',
        indexCount: 1,
    },
    {
        label: 'C2',
        uuid: 'da129a53-212a-46db-8ad1-deec4c20648b',
        indexCount: 1,
    },
    {
        label: 'A1',
        uuid: 'eed6e74f-3dac-46bf-9b30-6a7a385a3f1f',
        indexCount: 1,
    },
];

vi.mock('@dbco/portal-api/client/place.api', () => ({
    getSections: vi.fn(() =>
        Promise.resolve({
            sections: placeSections,
        })
    ),
    createPlaceSections: vi.fn(() =>
        Promise.resolve({
            sections: [
                {
                    label: 'D1',
                    uuid: 'cebbacd8-cb94-4d53-80a7-41e782eaf8b6',
                    indexCount: 0,
                },
            ],
        })
    ),
}));

vi.mock('@dbco/portal-api/client/context.api', () => ({
    getSections: vi.fn(() =>
        Promise.resolve({
            sections: contextSections,
        })
    ),
    linkSection: vi.fn(() => Promise.resolve({})),
    unlinkSection: vi.fn(() => Promise.resolve({})),
}));

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(ContextSectionsForm, {
        localVue,
        propsData: props,
        attachTo: createContainer(), // supresses [BootstrapVue warn]: tooltip - The provided target is no valid HTML element.
    });
});

describe('ContextSectionsForm.vue', () => {
    it('should not render edit icon if place is verified', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
                isVerified: true,
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        await flushCallStack();

        const editIcon = wrapper.find('[data-testid="edit-icon"]');

        expect(editIcon.exists()).toBe(false);
    });

    it('should not render edit icon if place is not editable', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
                editable: false,
                isVerified: false,
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        await flushCallStack();

        const editIcon = wrapper.find('[data-testid="edit-icon"]');

        expect(editIcon.exists()).toBe(false);
    });

    it('should render edit icon if place is editable', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
                editable: true,
                isVerified: false,
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        await flushCallStack();

        const editIcon = wrapper.find('[data-testid="edit-icon"]');

        expect(editIcon.exists()).toBe(true);
    });

    it('should show "geverifieerd" when context is verified', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        await flushCallStack();

        const placeSectionLabels = wrapper.vm.filteredSections.map((section: any) => {
            return section.label;
        });

        // Expect array to be sorted alphabetically
        expect(placeSectionLabels).toStrictEqual(
            placeSectionLabels.sort((a: any, b: any) => {
                if (a.label < b.label) {
                    return -1;
                } else if (a.label > b.label) {
                    return 1;
                }
                return 0;
            })
        );
    });

    it('should not display save button when searchString is similar to an existing label', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        await flushCallStack();

        const searchStringInput = wrapper.find('[data-testid="search-string-input"]');
        await searchStringInput.setValue('C1');

        // This field has a ~DEBOUNCE~ of 300ms
        await new Promise((r) => setTimeout(r, 300));

        const addSectionButton = wrapper.find('[data-testid="add-section-button-wrapper"]');

        expect(addSectionButton.exists()).toBe(false);
    });

    it('should show savebutton when entered searchString is not similar to an existing label', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        await flushCallStack();

        const searchStringInput = wrapper.find('[data-testid="search-string-input"]');
        await searchStringInput.setValue('D1');

        // This field has a ~DEBOUNCE~ of 300ms
        await new Promise((r) => setTimeout(r, 300));

        const addSectionButton = wrapper.find('[data-testid="add-section-button-wrapper"]');

        expect(addSectionButton.exists()).toBe(true);
    });

    it('when a section is added it should show up in placeSections and contextSections and the query should be reset', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        await flushCallStack();

        const searchStringInput = wrapper.find('[data-testid="search-string-input"]');
        await searchStringInput.setValue('D1');

        // This field has a ~DEBOUNCE~ of 300ms
        await new Promise((r) => setTimeout(r, 300));

        const addSectionButton = wrapper.find('[data-testid="add-section-button"]');
        await addSectionButton.trigger('click');

        await wrapper.vm.$nextTick();

        const placeSectionLabels = wrapper.vm.placeSections.map((section: any) => {
            return section.label;
        });

        const contextSectionlabels = wrapper.vm.contextSections.map((section: any) => {
            return section.label;
        });

        expect(placeSectionLabels).toEqual(expect.arrayContaining(['D1']));
        expect(contextSectionlabels).toEqual(expect.arrayContaining(['D1']));
        expect(wrapper.vm.searchString).toEqual('');
        expect((searchStringInput.element as HTMLInputElement).value).toEqual('');
    });

    it('when a section is being checked it should be linked', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        // Yes, three ticks are needed to render
        await flushCallStack();

        const checkboxWrapper = wrapper.find('[data-testlabel="A3"]');
        const checkbox = checkboxWrapper.find('input[type="checkbox"]');

        await checkbox.trigger('click');

        const contextSectionlabels = wrapper.vm.contextSections.map((section: any) => {
            return section.label;
        });
        expect(contextSectionlabels).toEqual(expect.arrayContaining(['A3']));
    });

    it('when a section is being unchecked it should be unlinked', async () => {
        const props = {
            place: {
                uuid: 'd2d46de4-c2b4-49af-9929-ba5e1d62443f',
            },
            contextUuid: '5d00abaa-e835-4877-a135-99a13f2470a6',
        };

        const wrapper = createComponent(props);

        // Yes, three ticks are needed to render
        await flushCallStack();

        const checkboxWrapper = wrapper.find('[data-testlabel="A2"]');
        const checkbox = checkboxWrapper.find('input[type="checkbox"]');

        await checkbox.trigger('click');

        const contextSectionlabels = wrapper.vm.contextSections.map((section: any) => {
            return section.label;
        });
        expect(contextSectionlabels).not.toEqual(expect.arrayContaining(['A2']));
    });
});

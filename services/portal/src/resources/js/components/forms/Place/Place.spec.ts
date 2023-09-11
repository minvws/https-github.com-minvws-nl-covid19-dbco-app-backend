import { createContainer, fakerjs, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';

import Place from '@/components/forms/Place/Place.vue';
import type { VueConstructor } from 'vue';
import { ContextCategoryV1, contextCategoryV1Options } from '@dbco/enum';
import { fakeAddress } from '@/utils/__fakes__/address';
import { startCase } from 'lodash';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(Place, {
        localVue,
        propsData: props,
        attachTo: createContainer(), // supresses [BootstrapVue warn]: tooltip - The provided target is no valid HTML element.
    });
});

describe('Place.vue', () => {
    it('should display the proper image for the class that is passed', () => {
        const props = {
            value: {
                address: fakeAddress(),
                category: 'shouldBeOnbekendHere',
            },
            isCancellable: true,
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('i').attributes('class')).toContain('icon--category-onbekend');
    });

    it('should display the count with "1 index" if there is 1 index', () => {
        // ARRANGE
        const props = {
            value: {
                address: fakeAddress(),
                indexCount: 1,
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('span').text()).toContain('1 index');
    });

    it('should display a count with "(count in numbers) indexen" if there is > 1 indexen', () => {
        // ARRANGE
        const props = {
            value: {
                address: fakeAddress(),
                indexCount: 2,
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('span').text()).toContain('2 indexen');
    });

    it('should display no count when value.indexCount = 0', () => {
        // ARRANGE
        const props = {
            value: {
                address: fakeAddress(),
                indexCount: 0,
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('span[data-testid="indexCount-span"]').exists()).toBe(false);
    });

    it('should display addressLabel if addressLabel is set', () => {
        // ARRANGE
        const label = 'Testlabel';
        const props = {
            value: {
                addressLabel: label,
                address: fakeAddress(),
                indexCount: 0,
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('[data-testid="address"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="address"]').attributes('text')).toBe(label);
    });

    it('should display address if addressLabel is NOT set, but address object is', () => {
        // ARRANGE
        const fakeAddressObject = fakeAddress();
        const props = {
            value: {
                address: fakeAddressObject,
                indexCount: 0,
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('[data-testid="address"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="address"]').attributes('text')).toContain(fakeAddressObject.street);
    });

    it('should NOT display an address if there is no addressLabel or address object', () => {
        // ARRANGE
        const props = {
            value: {
                indexCount: 0,
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('[data-testid="address"]').exists()).toBe(false);
    });

    it('should show "Ontkoppelen" button if prop "isCancellable" is true', () => {
        // ARRANGE
        const props = {
            address: fakeAddress(),
            isCancellable: true,
            value: {},
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('BButton-stub[variant="outline-danger"]').text()).toBe('Ontkoppelen');
    });

    it('should hide "Ontkoppelen" button if prop "isCancellable" is by default false', () => {
        // ARRANGE
        const props = {
            value: {},
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('BButton-stub[variant="outline-danger"]').exists()).toBe(false);
    });

    it('should call "showModal" on clicking on "Ontkoppelen"-button', async () => {
        // ARRANGE
        const props = {
            value: {
                address: fakeAddress(),
                indexCount: 1,
            },
            isCancellable: true,
            searchString: 'zzzzz',
        };

        const wrapper = createComponent(props);

        const spy = vi.spyOn(wrapper.vm as any, 'showModal');
        // Reset spy
        spy.mockReset();

        // ACT
        expect(spy).toHaveBeenCalledTimes(0);
        await wrapper.find('BButton-stub[variant="outline-danger"]').trigger('click');

        // ASSERT
        expect(spy).toHaveBeenCalledTimes(1);
    });

    it('showmodal should emit "cancel" on modal confirm', async () => {
        // GIVEN the component renders a cancellable Place
        const wrapper = createComponent({
            value: {},
            isCancellable: true,
        });

        // WHEN the modal confirms after opening
        wrapper.vm.$modal = {
            show: vi.fn((modalDefenition) => modalDefenition.onConfirm()),
        };
        // AND the modal is opened
        await wrapper.find('BButton-stub[variant="outline-danger"]').trigger('click');

        // THEN the component should have emitted 'cancel'
        expect(wrapper.emitted().cancel).toBeTruthy();
    });

    it('should show "geverifieerd" when context is verified', () => {
        // GIVEN the place to be verified
        const props = {
            value: {
                address: fakeAddress(),
                isVerified: true,
            },
        };

        // WHEN the component renders the place
        const wrapper = createComponent(props);

        // THEN the component shows the place to be verified
        expect(wrapper.find('.verified').text()).toBe('geverifieerd');
    });

    it('should disable "edit-place-button" when disabled prop is true', () => {
        const props = {
            value: {
                address: fakeAddress(),
                isVerified: true,
            },
            isEditable: true,
            isCancellable: true,
            disabled: true,
        };

        const wrapper = createComponent(props);

        expect(wrapper.find("[data-testid='edit-place-button']").attributes().disabled).toBe('true');
        expect(wrapper.find("[data-testid='deconnect-place-button']").attributes().disabled).toBe('true');
    });

    it('should disable "edit-place-button" when disabled prop is false', () => {
        const props = {
            value: {
                address: fakeAddress(),
                isVerified: true,
            },
            isEditable: true,
            isCancellable: true,
            disabled: false,
        };

        const wrapper = createComponent(props);

        expect(wrapper.find("[data-testid='edit-place-button']").attributes().disabled).toBe(undefined);
        expect(wrapper.find("[data-testid='deconnect-place-button']").attributes().disabled).toBe(undefined);
    });

    it('should display the correct category label', () => {
        const props = {
            value: {
                address: fakeAddress(),
                category: ContextCategoryV1.VALUE_bezoek,
            },
            isCancellable: true,
        };

        const wrapper = createComponent(props);
        const expectedLabel = contextCategoryV1Options.find((option) => option.value === props.value.category)?.label;

        // ASSERT
        expect(wrapper.find('span[data-testid="category-span"]').exists()).toBe(true);
        expect(wrapper.find('span[data-testid="category-span"]').text()).toBe(expectedLabel);
    });

    it('should display the category with correct formatting when there is no matching label', () => {
        const props = {
            value: {
                address: fakeAddress(),
                category: fakerjs.lorem.word(),
            },
            isCancellable: true,
        };

        const wrapper = createComponent(props);
        const expectedLabel = startCase(props.value.category);

        // ASSERT
        expect(wrapper.find('span[data-testid="category-span"]').exists()).toBe(true);
        expect(wrapper.find('span[data-testid="category-span"]').text()).toBe(expectedLabel);
    });

    it('should not display a separator between category and address when the address is only a postalcode', () => {
        const fakeAddressObject = fakeAddress();
        const props = {
            value: {
                address: {
                    postalCode: fakeAddressObject.postalCode,
                },
                category: ContextCategoryV1.VALUE_bezoek,
            },
            isCancellable: true,
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('span[data-testid="category-separator-span"]').exists()).toBe(false);
    });
});

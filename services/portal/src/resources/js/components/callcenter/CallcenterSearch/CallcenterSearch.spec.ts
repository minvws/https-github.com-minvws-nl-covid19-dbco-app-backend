import { RequestState } from '@/store/callcenter/callcenterStore';
import { setupTest } from '@/utils/test';
import { callcenterApi } from '@dbco/portal-api';
import { faker } from '@faker-js/faker';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import CallcenterSearch from './CallcenterSearch.vue';

const fakeFormData = {
    dateOfBirth: faker.date.past(),
    lastThreeBsnDigits: faker.string.numeric(3),
    postalCode: faker.location.zipCode('####??'),
    houseNumber: faker.location.buildingNumber(),
    houseNumberSuffix: faker.location.secondaryAddress(),
    lastname: '',
    phone: '',
};

const createComponent = setupTest((localVue: VueConstructor) => {
    localVue.directive('mask', vi.fn());
    return mount(CallcenterSearch, {
        localVue,
        pinia: createTestingPinia({
            initialState: {
                callcenter: {
                    searchState: RequestState.Idle,
                    searchResults: [],
                },
            },
            stubActions: false,
        }),
        stubs: { BModal: true },
    });
});

describe('CallcenterSearch.vue', () => {
    it('should render CallcenterSearch with initial form fields', () => {
        const wrapper = createComponent();
        const expectedFields = ['dateOfBirth', 'lastThreeBsnDigits', 'postalCode', 'houseNumber', 'houseNumberSuffix'];

        // Check that all fields are rendered
        expectedFields.forEach((field) => expect(wrapper.find(`[name="${field}`).isVisible()).toBe(true));
        // Make sure only the expected fields are visible
        expect(wrapper.findAll('input').filter((field) => field.isVisible()).length).toBe(expectedFields.length);
    });

    it('should show warning modal when no bsn link is clicked', async () => {
        const wrapper = createComponent();
        const noBsnLink = wrapper.find('[data-testid="no-bsn-link"]');

        await noBsnLink.trigger('click');

        const bsnSearchModal = wrapper.findComponent({ name: 'BModal' });
        expect(bsnSearchModal.isVisible()).toBe(true);
    });

    it('should disable bsn when okay button in bsn warning modal is clicked', async () => {
        const wrapper = createComponent();
        const noBsnLink = wrapper.find('[data-testid="no-bsn-link"]');

        await noBsnLink.trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        expect(wrapper.find('[name="lastThreeBsnDigits"]').attributes('disabled')).toBe('disabled');
    });

    it('should not disable bsn when cancel button in bsn warning modal is clicked', async () => {
        const wrapper = createComponent();
        const noBsnLink = wrapper.find('[data-testid="no-bsn-link"]');

        await noBsnLink.trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('cancel');

        expect(wrapper.find('[name="lastThreeBsnDigits"]').attributes('disabled')).toBeUndefined();
    });

    it('should show lastname when okay button in bsn warning modal is clicked', async () => {
        const wrapper = createComponent();
        const noBsnLink = wrapper.find('[data-testid="no-bsn-link"]');

        await noBsnLink.trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        expect(wrapper.find('[name="lastname"]').isVisible()).toBe(true);
    });

    it('should not show lastname when cancel button in bsn warning modal is clicked', async () => {
        const wrapper = createComponent();
        const noBsnLink = wrapper.find('[data-testid="no-bsn-link"]');

        await noBsnLink.trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('cancel');

        expect(wrapper.find('[name="lastname"]').isVisible()).toBe(false);
    });

    it('should show phone number when no address is clicked', async () => {
        const wrapper = createComponent();
        const noAddressLink = wrapper.find('[data-testid="no-address-link"]');

        await noAddressLink.trigger('click');
        expect(wrapper.find('[name="phone"]').isVisible()).toBe(true);
    });

    it('should disable address when no address is clicked', async () => {
        const wrapper = createComponent();
        const noAddressLink = wrapper.find('[data-testid="no-address-link"]');

        const addressFields = wrapper.findAll('.address input');
        expect(addressFields.filter((field) => field.attributes('disabled')).length).toBe(0);

        await noAddressLink.trigger('click');

        expect(addressFields.filter((field) => field.attributes('disabled')).length).toBe(addressFields.length);
    });

    it('should re-enable bsn and keep lastname when has bsn is clicked', async () => {
        const wrapper = createComponent();
        const noBsnLink = wrapper.find('[data-testid="no-bsn-link"]');

        await noBsnLink.trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');
        await noBsnLink.trigger('click');

        expect(wrapper.find('[name="lastThreeBsnDigits"]').attributes('disabled')).toBeUndefined();

        const lastnameField = wrapper.find('[name="lastname"]');
        expect(lastnameField.isVisible()).toBe(true);
        expect(lastnameField.attributes('disabled')).toBeUndefined();
    });

    it('should re-enable address and keep phone when has address is clicked', async () => {
        const wrapper = createComponent();
        const noAddressLink = wrapper.find('[data-testid="no-address-link"]');

        await noAddressLink.trigger('click');
        await noAddressLink.trigger('click');

        const addressFields = wrapper.findAll('.address input');
        expect(addressFields.filter((field) => field.attributes('disabled')).length).toBe(0);

        const phoneField = wrapper.find('[name="phone"]');
        expect(phoneField.isVisible()).toBe(true);
        expect(phoneField.attributes('disabled')).toBeUndefined();
    });

    it('should render submit with "Zoeken" by default', () => {
        const wrapper = createComponent();
        const searchButton = wrapper.find('[data-testid="search-button"]');
        expect(searchButton.text()).toBe('Zoeken');
    });

    it('should render submit with "Opnieuw zoeken" after a search', async () => {
        const wrapper = createComponent();
        const searchButton = wrapper.find('[data-testid="search-button"]');

        await wrapper.vm.search();

        expect(searchButton.text()).toBe('Opnieuw zoeken');
    });

    it('should show lastname and phone after search attempt if no results are found', async () => {
        // Api mock required once DBCO-4123 is complete - currently sets results to empty by default
        const wrapper = createComponent();

        await wrapper.vm.search();

        const lastnameField = wrapper.find('[name="lastname"]');
        expect(lastnameField.isVisible()).toBe(true);
        expect(lastnameField.attributes('disabled')).toBeUndefined();

        const phoneField = wrapper.find('[name="phone"]');
        expect(phoneField.isVisible()).toBe(true);
        expect(phoneField.attributes('disabled')).toBeUndefined();
    });

    it('stop button should be disabled by default', () => {
        const wrapper = createComponent();
        expect(wrapper.find('[data-testid="stop-button"]').attributes('disabled')).toBe('disabled');
    });

    it('should enable stop button to clear form when form has values', async () => {
        const wrapper = createComponent();
        await wrapper.find('[name="dateOfBirth"]').setValue(faker.date.past().toDateString());
        expect(wrapper.find('[data-testid="stop-button"]').attributes('disabled')).toBeUndefined();
    });

    it('should clear the form when stop button is clicked', async () => {
        const wrapper = createComponent();
        const stopButton = wrapper.find('[data-testid="stop-button"]');
        const formFields = wrapper.findAll('input').filter((field) => field.isVisible());

        await stopButton.trigger('click');

        expect(stopButton.attributes('disabled')).toBe('disabled');
        for (let index = 0; index < formFields.length; index++) {
            expect((formFields.at(index).element as HTMLInputElement).value).toBe('');
        }
    });

    it('should update text when no BSN link is toggled', async () => {
        const wrapper = createComponent();
        const noBsnLink = wrapper.find('[data-testid="no-bsn-link"]');

        expect(noBsnLink.text()).toBe('Deze persoon heeft geen BSN');

        await noBsnLink.trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        expect(noBsnLink.text()).toBe('Deze persoon heeft wel een BSN');

        await noBsnLink.trigger('click');

        expect(noBsnLink.text()).toBe('Deze persoon heeft geen BSN');
    });

    it('should update text when no address link is toggled', async () => {
        const wrapper = createComponent();
        const noBsnLink = wrapper.find('[data-testid="no-address-link"]');

        expect(noBsnLink.text()).toBe('Deze persoon heeft geen adres');

        await noBsnLink.trigger('click');

        expect(noBsnLink.text()).toBe('Deze persoon heeft wel een adres');

        await noBsnLink.trigger('click');

        expect(noBsnLink.text()).toBe('Deze persoon heeft geen adres');
    });

    it('should reset has no BSN / address links when stop button is clicked', async () => {
        const wrapper = createComponent();
        const spyOnReset = vi.spyOn(wrapper.vm.$formulate, 'reset');
        wrapper.vm.formValues = fakeFormData;

        const noBsnLink = wrapper.find('[data-testid="no-bsn-link"]');
        const noAddressLink = wrapper.find('[data-testid="no-address-link"]');
        const stopButton = wrapper.find('[data-testid="stop-button"]');

        await noBsnLink.trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');
        await noAddressLink.trigger('click');

        // Texts when toggled
        expect(noBsnLink.text()).toBe('Deze persoon heeft wel een BSN');
        expect(noAddressLink.text()).toBe('Deze persoon heeft wel een adres');

        // Should reset texts to default when stop button is clicked
        await stopButton.trigger('click');

        expect(noBsnLink.text()).toBe('Deze persoon heeft geen BSN');
        expect(noAddressLink.text()).toBe('Deze persoon heeft geen adres');

        expect(spyOnReset).toBeCalledTimes(1);
    });

    it('should reset extra fields when stop button is clicked', async () => {
        const lastname = faker.person.lastName();
        const phone = faker.phone.number();
        const wrapper = createComponent();
        const spyOnReset = vi.spyOn(wrapper.vm.$formulate, 'reset');
        wrapper.vm.formValues = {
            ...fakeFormData,
            lastname,
            phone,
        };

        const lastnameField = wrapper.find<HTMLInputElement>('[name="lastname"]');
        const phoneField = wrapper.find<HTMLInputElement>('[name="phone"]');

        await wrapper.find('[data-testid="no-bsn-link"]').trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');
        await wrapper.find('[data-testid="no-address-link"]').trigger('click');

        expect(lastnameField.isVisible()).toBe(true);
        expect(lastnameField.element.value).toBe(lastname);

        expect(phoneField.isVisible()).toBe(true);
        expect(phoneField.element.value).toBe(phone);

        const stopButton = wrapper.find('[data-testid="stop-button"]');
        await stopButton.trigger('click');

        expect(lastnameField.isVisible()).toBe(false);
        expect(lastnameField.element.value).toBe('');

        expect(phoneField.isVisible()).toBe(false);
        expect(phoneField.element.value).toBe('');

        expect(spyOnReset).toBeCalledTimes(1);
    });

    it('should reset search button text when stop button is clicked', async () => {
        const wrapper = createComponent();
        wrapper.vm.formValues = fakeFormData;

        await wrapper.vm.search();
        await wrapper.find('[data-testid="stop-button"]').trigger('click');

        expect(wrapper.find('[data-testid="search-button"]').text()).toBe('Zoeken');
    });

    it('should only include required dateOfBirth in post data by default', async () => {
        const wrapper = createComponent();
        const spyOnSearch = vi.spyOn(callcenterApi, 'search');

        await wrapper.vm.search();

        expect(spyOnSearch).toBeCalledWith({ dateOfBirth: '' });
    });

    it('should only post lastThreeBsnDigits if included in search', async () => {
        const wrapper = createComponent();
        const spyOnSearch = vi.spyOn(callcenterApi, 'search');
        const lastThreeBsnDigits = faker.string.numeric(3);

        await wrapper.find('[name="lastThreeBsnDigits"]').setValue(lastThreeBsnDigits);
        await wrapper.vm.search();

        expect(spyOnSearch).toBeCalledWith({ lastThreeBsnDigits, dateOfBirth: '' });
    });

    it('should only post address if included in search', async () => {
        const wrapper = createComponent();
        const spyOnSearch = vi.spyOn(callcenterApi, 'search');
        const postalCode = faker.location.zipCode('####??');
        const houseNumber = faker.location.buildingNumber();
        const houseNumberSuffix = faker.location.secondaryAddress();

        await wrapper.find('[name="postalCode"]').setValue(postalCode);
        await wrapper.find('[name="houseNumber"]').setValue(houseNumber);
        await wrapper.find('[name="houseNumberSuffix"]').setValue(houseNumberSuffix);
        await wrapper.vm.search();

        expect(spyOnSearch).toBeCalledWith({
            postalCode,
            houseNumber,
            houseNumberSuffix,
            dateOfBirth: '',
        });
    });

    it('should only post lastname if included in search', async () => {
        const wrapper = createComponent();
        const spyOnSearch = vi.spyOn(callcenterApi, 'search');
        const lastname = faker.person.lastName();

        await wrapper.find('[data-testid="no-bsn-link"]').trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        await wrapper.find('[name="lastname"]').setValue(lastname);
        await wrapper.vm.search();

        expect(spyOnSearch).toBeCalledWith({ lastname, dateOfBirth: '' });
    });

    it('should only post phone if included in search', async () => {
        const wrapper = createComponent();
        const spyOnSearch = vi.spyOn(callcenterApi, 'search');
        const phone = faker.phone.number();

        await wrapper.find('[data-testid="no-bsn-link"]').trigger('click');
        await wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        await wrapper.find('[name="phone"]').setValue(phone);
        await wrapper.vm.search();

        expect(spyOnSearch).toBeCalledWith({ phone, dateOfBirth: '' });
    });

    it('should post lastThreeBsnDigits if lastThreeBsnDigits is disabled', async () => {
        const wrapper = createComponent();
        const spyOnSearch = vi.spyOn(callcenterApi, 'search');
        const lastThreeBsnDigits = fakeFormData.lastThreeBsnDigits;

        await wrapper.find('[name="lastThreeBsnDigits"]').setValue(lastThreeBsnDigits);
        wrapper.vm.hasLastThreeBsnDigits = false;
        await wrapper.vm.search();

        expect(spyOnSearch).toBeCalledWith({ dateOfBirth: '', lastThreeBsnDigits });
    });

    it('should post address if address is disabled', async () => {
        const wrapper = createComponent();
        const spyOnSearch = vi.spyOn(callcenterApi, 'search');
        const postalCode = faker.location.zipCode('####??');
        const houseNumber = faker.location.buildingNumber();
        const houseNumberSuffix = faker.location.secondaryAddress();

        await wrapper.find('[name="postalCode"]').setValue(postalCode);
        await wrapper.find('[name="houseNumber"]').setValue(houseNumber);
        await wrapper.find('[name="houseNumberSuffix"]').setValue(houseNumberSuffix);
        wrapper.vm.hasAddress = false;
        await wrapper.vm.search();

        expect(spyOnSearch).toBeCalledWith({ dateOfBirth: '', postalCode, houseNumber, houseNumberSuffix });
    });
});

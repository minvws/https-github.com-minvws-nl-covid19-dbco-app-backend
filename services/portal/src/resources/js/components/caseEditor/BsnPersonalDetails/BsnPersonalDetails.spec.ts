import { fakerjs, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import BsnPersonalDetails from './BsnPersonalDetails.vue';
import Vuex from 'vuex';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';

const mockAgeValue = 18;
const createComponent = setupTest(
    (localVue: VueConstructor, indexStoreState: Partial<IndexStoreState> = {}, givenProps?: object) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        return shallowMount<BsnPersonalDetails>(BsnPersonalDetails, {
            localVue,
            propsData: givenProps,
            store: new Vuex.Store({
                modules: {
                    index: indexStoreModule,
                },
            }),
            mocks: { $filters: { age: vi.fn(() => mockAgeValue), dateFormatMonth: vi.fn((value) => value) } },
        });
    }
);

describe('BsnPersonalDetails.vue', () => {
    it('should show display name if given', () => {
        const firstname = fakerjs.person.firstName();
        const lastname = fakerjs.person.lastName();

        const wrapper = createComponent({}, { firstname, lastname });
        expect(wrapper.findByTestId('display-name').text()).toContain(`${firstname} ${lastname}`);
    });

    it('should show dateOfBirth if given', () => {
        const dateOfBirth = fakerjs.date.past().toDateString();

        const wrapper = createComponent({}, { dateOfBirth });
        expect(wrapper.findByTestId('date-of-birth').text()).toContain(`${mockAgeValue} jaar (${dateOfBirth})`);
    });

    it('should show bsnCensored if given', () => {
        const bsnCensored = fakerjs.string.numeric(8);

        const wrapper = createComponent({}, { bsnCensored });
        expect(wrapper.findByTestId('bsn-censored').text()).toContain(bsnCensored);
    });

    it('should show address if given', () => {
        const address = {
            postalCode: fakerjs.location.zipCode(),
            houseNumber: fakerjs.location.buildingNumber(),
            houseNumberSuffix: fakerjs.location.secondaryAddress(),
            street: fakerjs.location.street(),
            town: fakerjs.location.city(),
        };

        const wrapper = createComponent({}, { address });
        const addressText = wrapper.findByTestId('address').text();
        expect(addressText).toContain(address.postalCode);
        expect(addressText).toContain(address.houseNumber);
        expect(addressText).toContain(address.houseNumberSuffix);
        expect(addressText).toContain(address.street);
        expect(addressText).toContain(address.town);
    });

    it('should show dob "Matcht met BSN" for schema versions >= 8 if bsnCensored is given', () => {
        const address = { postalCode: fakerjs.location.zipCode() };
        const bsnCensored = fakerjs.string.numeric(8);

        const wrapper = createComponent({ meta: { schemaVersion: 8 } }, { address, bsnCensored });
        expect(wrapper.findAllByTestId('bsn-verified').exists()).toBe(false);
    });

    it('should NOT show dob "Matcht met BSN" for schema versions < 8 if bsnCensored is given', () => {
        const bsnCensored = fakerjs.string.numeric(8);

        const wrapper = createComponent({ meta: { schemaVersion: 7 } }, { bsnCensored });
        expect(wrapper.findAllByTestId('bsn-verified').exists()).toBe(false);
    });

    it('should show address "Matcht met BSN" for schema versions >= 8 if bsnCensored is given and addressVerified is true', () => {
        const address = { postalCode: fakerjs.location.zipCode() };
        const bsnCensored = fakerjs.string.numeric(8);

        const wrapper = createComponent(
            { meta: { schemaVersion: 8 } },
            { address, bsnCensored, addressVerified: true }
        );

        expect(wrapper.findAllByTestId('address-verified').exists()).toBe(true);
    });

    it('should show address "Matcht met BSN" for schema versions < 8 if bsnCensored is given and addressVerified is true', () => {
        const address = { postalCode: fakerjs.location.zipCode() };
        const bsnCensored = fakerjs.string.numeric(8);

        const wrapper = createComponent(
            { meta: { schemaVersion: 7 } },
            { address, bsnCensored, addressVerified: true }
        );
        expect(wrapper.findAllByTestId('address-verified').exists()).toBe(false);
    });

    it('should show "Adres bewerken" for schema versions >= 8 if bsnCensored is given and addressVerified is false', () => {
        const address = { postalCode: fakerjs.location.zipCode() };
        const bsnCensored = fakerjs.string.numeric(8);

        const wrapper = createComponent(
            { meta: { schemaVersion: 8 } },
            { address, bsnCensored, addressVerified: false }
        );
        expect(wrapper.findAllByTestId('edit-address-link').exists()).toBe(true);
    });

    it('should NOT show "Adres bewerken" for schema versions < 8 if bsnCensored is given and addressVerified is false', () => {
        const address = { postalCode: fakerjs.location.zipCode() };
        const bsnCensored = fakerjs.string.numeric(8);

        const wrapper = createComponent(
            { meta: { schemaVersion: 7 } },
            { address, bsnCensored, addressVerified: false }
        );
        expect(wrapper.findAllByTestId('edit-address-link').exists()).toBe(false);
    });
});

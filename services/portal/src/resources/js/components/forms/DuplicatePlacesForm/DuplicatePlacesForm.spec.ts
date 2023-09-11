import Vuex from 'vuex';

import placeStore from '@/store/place/placeStore';

import { shallowMount } from '@vue/test-utils';

import type { LocationDTO, PlaceDTO } from '@dbco/portal-api/place.dto';

import DuplicatePlacesForm from '@/components/forms/DuplicatePlacesForm/DuplicatePlacesForm.vue';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const props = {
    duplicatePlaces: [
        {
            uuid: '5a160898-5b71-42f6-9506-04735823066d',
            label: 'Restaurant het Kroontje',
            category: 'restaurant',
            categoryLabel: null,
            indexCountSinceReset: 0,
            indexCountResetAt: null,
            address: {
                street: '',
                houseNumber: '1',
                houseNumberSuffix: undefined,
                postalCode: '1234AB',
                town: '',
                country: 'NL',
            },
            addressLabel: '1, 1234AB',
            indexCount: 1,
            isVerified: false,
            source: 'manual' as PlaceDTO['source'],
            ggd: { code: null, municipality: null },
            organisationUuidByPostalCode: null,
            organisationUuid: null,
            createdAt: '2022-02-07T11:01:31Z',
            updatedAt: '2022-02-10T14:44:31Z',
            lastIndexPresence: fakerjs.date.recent().toString(),
            situationNumbers: [],
            sections: [],
        },
        {
            uuid: 'as342198-5b71-42f6-9506-04735823066d',
            label: 'Kroon Hotel',
            category: 'restaurant',
            categoryLabel: null,
            indexCountSinceReset: 0,
            indexCountResetAt: null,
            address: {
                street: '',
                houseNumber: '33',
                houseNumberSuffix: undefined,
                postalCode: '1123ER',
                town: '',
                country: 'NL',
            },
            addressLabel: '33, 1123ER',
            indexCount: 1,
            isVerified: false,
            source: 'manual' as PlaceDTO['source'],
            ggd: { code: null, municipality: null },
            organisationUuidByPostalCode: null,
            organisationUuid: null,
            createdAt: '2022-02-07T11:01:31Z',
            updatedAt: '2022-02-10T14:44:31Z',
            lastIndexPresence: fakerjs.date.recent().toString(),
            situationNumbers: [],
            sections: [],
        },
    ],
    newPlace: {
        uuid: '',
        label: 'Ristorante Corona',
        category: 'restaurant',
        categoryLabel: null,
        indexCountSinceReset: 0,
        indexCountResetAt: null,
        address: {
            street: '',
            houseNumber: '12',
            houseNumberSuffix: undefined,
            postalCode: '3421BC',
            town: '',
            country: 'NL',
        },
        addressLabel: '12, 4321BC',
        indexCount: 0,
        isVerified: false,
        source: 'manual' as PlaceDTO['source'],
        ggd: { code: null, municipality: null },
        organisationUuidByPostalCode: null,
        organisationUuid: null,
        createdAt: '',
        updatedAt: '',
        lastIndexPresence: fakerjs.date.recent().toString(),
        situationNumbers: [],
        sections: [],
    },
};

const createComponent = setupTest(
    (localVue: VueConstructor, props?: { duplicatePlaces: PlaceDTO[]; newPlace: Partial<PlaceDTO | LocationDTO> }) => {
        return shallowMount(DuplicatePlacesForm, {
            localVue,
            propsData: props,
            store: new Vuex.Store({
                modules: {
                    place: placeStore,
                },
            }),
            stubs: {
                BButton: true,
                Place: true,
            },
        });
    }
);

describe('DuplicatePlacesForm.vue', () => {
    it('should render each duplicate as a Place component', () => {
        const wrapper = createComponent(props);

        const places = wrapper.findAllComponents({ name: 'Place' });
        expect(places.at(0).props('value')).toStrictEqual(props.duplicatePlaces[0]);
        expect(places.at(1).props('value')).toStrictEqual(props.duplicatePlaces[1]);
    });

    it('should render newPlace as a Place component', () => {
        const wrapper = createComponent(props);

        const places = wrapper.findAllComponents({ name: 'Place' });
        expect(places.at(2).props('value')).toStrictEqual(props.newPlace);
    });

    it('should emit "selectPlace" when duplicate "kies" Button is clicked', async () => {
        const wrapper = createComponent(props);

        const buttons = wrapper.findAllComponents({ name: 'BButton' });
        await buttons.at(0).trigger('click');

        expect(wrapper.emitted('selectPlace')).toBeTruthy();
    });

    it('should emit "selectPlace" when newPlace "kies" Button is clicked', async () => {
        const wrapper = createComponent(props);

        const buttons = wrapper.findAllComponents({ name: 'BButton' });
        await buttons.at(2).trigger('click');
        await flushCallStack();

        expect(wrapper.emitted('selectPlace')).toBeTruthy();
    });
});

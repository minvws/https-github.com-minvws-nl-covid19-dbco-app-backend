import type { Wrapper, WrapperArray } from '@vue/test-utils';
import { shallowMount } from '@vue/test-utils';

import { placeApi } from '@dbco/portal-api';
import { setupTest, fakerjs } from '@/utils/test';
import PlacesMergeModal from './PlacesMergeModal.vue';
import type { VueConstructor } from 'vue';

const places = [
    {
        uuid: '01b63be3-monkey-see-monkey-do',
        label: 'Apenheul',
        category: 'dieren',
        categoryLabel: 'Dieren',
        address: {
            street: 'J.C. Wilslaan',
            houseNumber: '21',
            houseNumberSuffix: null,
            postalCode: '7313HK',
            town: 'Apeldoorn',
            country: 'NL',
        },
        addressLabel: 'J.C. Wilslaan 21, 7313HK Apeldoorn',
        ggd: {
            name: null,
            municipality: null,
        },
        indexCount: 1,
        isVerified: false,
        source: 'external',
        createdAt: '2021-11-02 13:06:54',
        updatedAt: '2021-11-02 13:06:54',
        lastIndexPresence: fakerjs.date.recent().toString(),
    },
    {
        uuid: '01b63be3-gaies-zo',
        label: 'GaiaZOO',
        category: 'dieren',
        categoryLabel: 'Dieren',
        address: {
            street: 'Gaiaboulevard',
            houseNumber: '1',
            houseNumberSuffix: null,
            postalCode: '6468PH',
            town: 'Kerkrade',
            country: 'NL',
        },
        addressLabel: 'Gaiaboulevard 1, 6468 PH Kerkrade',
        ggd: {
            name: null,
            municipality: null,
        },
        indexCount: 1,
        isVerified: false,
        source: 'external',
        createdAt: '2021-11-02 13:07:54',
        updatedAt: '2021-11-02 13:07:54',
        lastIndexPresence: fakerjs.date.recent().toString(),
    },
];

/**
 * Will attempt to find components and wait for it to appear before the timeout is reached,
 * can be convenient when awaiting the nextTick does not result to the expected DOM
 */
function waitForComponents(wrapper: Wrapper<Vue>, name: string, timeout = 500): Promise<WrapperArray<Vue>> {
    return new Promise((resolve, reject) => {
        const start = Date.now();
        const interval = setInterval(() => {
            const components = wrapper.findAllComponents({ name });
            if (!components.length) {
                if (Date.now() - start > timeout) {
                    clearInterval(interval);
                    reject(`Timeout of ${timeout}ms reached while looking for components with name ${name}`);
                }

                return;
            }

            clearInterval(interval);
            resolve(components);
        }, 10);
    });
}

const createComponent = setupTest((localVue: VueConstructor, props: Record<string, unknown> = {}) => {
    return shallowMount(PlacesMergeModal, {
        localVue,
        propsData: {
            places,
            ...props,
        },
        stubs: {
            BModal: true,
            Place: true,
        },
    });
});

describe('PlacesMergeModal.vue', () => {
    it('should be visible', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'BModal' }).exists()).toBe(true);
    });

    it('should use placeSearch', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'PlaceSearch' }).exists()).toBe(true);
    });

    it('should go to the next step', async () => {
        const wrapper = createComponent();

        const modal = wrapper.findComponent({ name: 'BModal' });

        await modal.vm.$emit('ok');

        expect(modal.props('title')).toBe(
            'Welke naam en adresgegevens wil je behouden? Let op: samenvoegen kan niet ongedaan worden gemaakt'
        );
        expect(wrapper.findComponent({ name: 'BFormRadioGroup' }).exists()).toBe(true);
    });

    it('should lock a place and select it by default in the second step', async () => {
        const wrapper = createComponent({
            lockedTargetUuid: places[1].uuid,
        });

        expect(wrapper.findComponent({ name: 'Place' }).props('value')).toBe(places[1]);

        const modal = wrapper.findComponent({ name: 'BModal' });

        expect(modal.exists()).toBe(true);

        await modal.vm.$emit('ok');

        const radioGroup = wrapper.findComponent({ name: 'BFormRadioGroup' });

        expect(radioGroup.props('checked')).toBe(places[1]);
    });

    it('should show confirmation info block and close modal on confirm', async () => {
        const wrapper = createComponent();

        const modal = wrapper.findComponent({ name: 'BModal' });

        expect(modal.exists()).toBe(true);

        await modal.vm.$emit('ok');

        const radioGroup = wrapper.findComponent({ name: 'BFormRadioGroup' });

        expect(radioGroup.props('checked')).toBe(places[0]);

        const mergeMock = vi.spyOn(placeApi, 'merge').mockResolvedValueOnce({} as any);

        await modal.vm.$emit('ok');

        expect(mergeMock).toHaveBeenCalled();

        // Wait for the modal to update
        await waitForComponents(wrapper, 'FormInfo');

        const text = wrapper.text();
        expect(text).toContain(places[0].label);
        expect(text).toContain(places[1].label);
        expect(text).toContain('Samengevoegd als');
        expect(text).toContain(places[0].address.street);
    });
});

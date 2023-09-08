import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import FormMedicineDatalist from './FormMedicineDatalist.vue';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(FormMedicineDatalist, {
        localVue,
    });
});

describe('FormMedicineDatalist.vue', () => {
    it('should Load the component with the data from the medicineList.json', () => {
        // ARRANGE
        const wrapper = createComponent();

        // ASSERT
        // I just picked 2 from the list to check for.
        expect(wrapper.find('option[value="Piroxicam (Pijnmedicatie), niet afweeronderdrukkend"]').exists()).toBe(true);
        expect(
            wrapper.find('option[value="Varenicline, Champix (Categorie: Overig), niet afweeronderdrukkend"]').exists()
        ).toBe(true);
    });
});

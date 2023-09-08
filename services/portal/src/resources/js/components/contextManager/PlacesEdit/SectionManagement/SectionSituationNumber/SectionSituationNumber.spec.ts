import { mount } from '@vue/test-utils';
import SectionSituationNumber from './SectionSituationNumber.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { fakePlace } from '@/utils/__fakes__/place';
import { createPinia, setActivePinia } from 'pinia';
import { createTestingPinia } from '@pinia/testing';
import { usePlaceSituationStore } from '@/store/placeSituation/placeSituationStore';
import { fakeSituation } from '@/utils/__fakes__/situation';

const createComponent = setupTest((localVue: VueConstructor) => {
    return mount<SectionSituationNumber>(SectionSituationNumber, {
        localVue,
        propsData: {
            placeUuid: fakePlace().uuid,
        },
        pinia: createTestingPinia({
            stubActions: false,
        }),
    });
});

describe('SectionSituationNumber.vue', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('should be visible', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'BForm' }).exists()).toBe(true);
    });

    it('should render new situation placeholder fields and remove situation fields on click button remove', async () => {
        const wrapper = createComponent();

        await wrapper.find('.add-situation-number-button').trigger('click');
        expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(true);

        await wrapper.find('.remove-situation-number-button').trigger('click');
        expect(wrapper.findComponent({ name: 'BFormInput' }).exists()).toBe(false);
    });

    it('should check if updateSituations has been updated with new situationNumbers', async () => {
        const wrapper = createComponent();
        const spyAction = vi.spyOn(usePlaceSituationStore(), 'updateSituations');
        const situationNumbers = [fakeSituation()];

        await wrapper.setData({ situationNumbers });

        expect(spyAction).toHaveBeenCalledTimes(1);
        expect(spyAction).toHaveBeenCalledWith(situationNumbers);
    });
});
